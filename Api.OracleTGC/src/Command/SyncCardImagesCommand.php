<?php

namespace App\Command;

use App\Domain\Factory\CardFactory;
use App\Repositories\CardRepository;
use App\Service\CardImageStorageService;
use App\Service\PokemonService;
use App\Service\ScryfallService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Sincroniza imagens e dados de cartas das APIs externas para o storage local e banco.
 * Após rodar, a API pode buscar cartas por set/número no banco (pesquisa binária por índice)
 * sem depender das APIs externas no momento do scan.
 */
#[AsCommand(
    name: 'app:cards:sync-images',
    description: 'Baixa imagens de cartas para storage local e persiste no banco para busca independente ao scanear.',
)]
class SyncCardImagesCommand extends Command
{
    public function __construct(
        private ScryfallService $scryfallService,
        private PokemonService $pokemonService,
        private CardImageStorageService $imageStorage,
        private CardRepository $cardRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('game', 'g', InputOption::VALUE_REQUIRED, 'Jogo: mtg, pokemon ou all', 'all')
            ->addOption('set', 's', InputOption::VALUE_REQUIRED, 'Código do set (opcional). Se não informado, sincroniza todos os sets.')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Máximo de cartas por set (0 = sem limite)', '0')
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Pular cartas que já existem no banco com imagem local');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $game = strtolower((string) $input->getOption('game'));
        $setFilter = $input->getOption('set') ? strtoupper((string) $input->getOption('set')) : null;
        $limitPerSet = max(0, (int) $input->getOption('limit'));
        $skipExisting = $input->getOption('skip-existing');

        $games = $game === 'all' ? ['mtg', 'pokemon'] : [$game];
        if (!in_array($game, ['mtg', 'pokemon', 'all'], true)) {
            $io->error('Opção --game deve ser: mtg, pokemon ou all.');
            return Command::FAILURE;
        }

        $totalSaved = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $totalImagesSkipped = 0; // cartas salvas no banco mas imagem não baixada (ex.: dev sem rede)

        foreach ($games as $g) {
            $sets = $g === 'mtg' ? $this->scryfallService->fetchAllSets() : $this->pokemonService->fetchAllSets();
            if ($setFilter) {
                $sets = array_filter($sets, fn(array $s) => ($s['code'] ?? '') === $setFilter);
            }
            $io->title("Sincronizando: " . strtoupper($g) . ' (' . count($sets) . ' sets)');

            foreach ($sets as $setInfo) {
                $setCode = $setInfo['code'] ?? '';
                $setName = $setInfo['name'] ?? $setCode;
                $io->section("Set: {$setCode} - {$setName}");

                $count = 0;
                $generator = $g === 'mtg'
                    ? $this->scryfallService->fetchCardsInSet($setCode)
                    : $this->pokemonService->fetchCardsInSet($setCode);

                foreach ($generator as $rawCard) {
                    if ($limitPerSet > 0 && $count >= $limitPerSet) {
                        break;
                    }
                    try {
                        $dto = $g === 'mtg'
                            ? $this->scryfallService->mapResponseToCardDTO($rawCard)
                            : $this->pokemonService->mapResponseToCardDTO($rawCard);
                    } catch (\Throwable $e) {
                        $totalErrors++;
                        continue;
                    }

                    $number = $dto->number;
                    $existing = $this->cardRepository->findBySetAndNumber($g, $setCode, $number);
                    if ($skipExisting && $existing !== null && $this->isLocalImageUrl($existing->getImageUrl())) {
                        $totalSkipped++;
                        $count++;
                        continue;
                    }

                    // Tenta baixar imagem para storage local (em dev pode falhar; mesmo assim salva a carta no banco)
                    $localPath = $this->imageStorage->downloadAndSave(
                        $dto->imageUrl,
                        $g,
                        $setCode,
                        $number
                    );

                    $card = $existing ?? CardFactory::createFromDTO($dto);
                    $card->setImageUrl($localPath ?? $dto->imageUrl); // local se baixou; senão mantém URL externa (útil em dev)
                    if ($localPath === null && !empty($dto->imageUrl)) {
                        $totalImagesSkipped++;
                    }

                    if ($dto->prices !== null) {
                        $this->applyPricesToCard($card, $dto->prices->usd, $dto->prices->usdFoil);
                    }

                    $this->cardRepository->save($card);
                    $totalSaved++;
                    $count++;
                }

                $io->text("Set {$setCode}: {$count} cartas processadas.");
            }
        }

        $messages = [
            "Concluído. Cartas salvas no banco: {$totalSaved}, Ignoradas: {$totalSkipped}, Erros: {$totalErrors}.",
        ];
        if ($totalImagesSkipped > 0) {
            $messages[] = "({$totalImagesSkipped} cartas com imagem da URL externa — download não disponível, ex.: ambiente dev)";
        }
        $messages[] = 'Use a API com busca por set/número para identificar cartas ao scanear (banco local).';
        $io->success($messages);
        return Command::SUCCESS;
    }

    private function isLocalImageUrl(?string $url): bool
    {
        return $url !== null && str_starts_with($url, CardImageStorageService::WEB_PATH_PREFIX);
    }

    private function applyPricesToCard(object $card, ?float $usd, ?float $eur): void
    {
        if ($usd !== null && method_exists($card, 'setPriceUsd')) {
            $card->setPriceUsd($usd);
        }
        if ($eur !== null && method_exists($card, 'setPriceEur')) {
            $card->setPriceEur($eur);
        }
    }
}
