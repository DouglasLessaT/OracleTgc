<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Card;
use App\Domain\Entity\CardMTG;
use App\Domain\Entity\CardPTCG;
use App\Domain\Entity\CardOPCG;
use InvalidArgumentException;

/**
 * Card Factory
 * 
 * Factory para criar instâncias de cards baseado no tipo de jogo.
 */
class CardFactory
{
    /**
     * Cria um card baseado no tipo de jogo
     * 
     * @param string $game Tipo do jogo: 'mtg', 'pokemon', 'onepiece'
     * @param array $data Dados do card
     * @return Card
     * @throws InvalidArgumentException
     */
    public static function create(string $game, array $data): Card
    {
        return match (strtolower($game)) {
            'mtg', 'magic' => self::createMTG($data),
            'pokemon', 'ptcg' => self::createPokemon($data),
            'onepiece', 'opcg' => self::createOnePiece($data),
            default => throw new InvalidArgumentException("Unknown game type: {$game}"),
        };
    }

    /**
     * Cria um card de Magic: The Gathering
     */
    public static function createMTG(array $data): CardMTG
    {
        $card = new CardMTG(
            name: $data['name'],
            setCode: $data['setCode'] ?? $data['set'] ?? '',
            setName: $data['setName'] ?? '',
            collectorNumber: $data['collectorNumber'] ?? $data['number'] ?? '',
            rarity: $data['rarity'] ?? 'common'
        );

        // Propriedades opcionais
        if (isset($data['imageUrl'])) {
            $card->setImageUrl($data['imageUrl']);
        }
        if (isset($data['manaCost'])) {
            $card->setManaCost($data['manaCost']);
        }
        if (isset($data['typeLine'])) {
            $card->setTypeLine($data['typeLine']);
        }
        if (isset($data['oracleText'])) {
            $card->setOracleText($data['oracleText']);
        }
        if (isset($data['power'])) {
            $card->setPower($data['power']);
        }
        if (isset($data['toughness'])) {
            $card->setToughness($data['toughness']);
        }
        if (isset($data['loyalty'])) {
            $card->setLoyalty($data['loyalty']);
        }
        if (isset($data['colors'])) {
            $card->setColors($data['colors']);
        }
        if (isset($data['colorIdentity'])) {
            $card->setColorIdentity($data['colorIdentity']);
        }
        if (isset($data['artist'])) {
            $card->setArtist($data['artist']);
        }
        if (isset($data['isFoil'])) {
            $card->setIsFoil($data['isFoil']);
        }
        if (isset($data['priceUsd'])) {
            $card->setPriceUsd($data['priceUsd']);
        }
        if (isset($data['priceEur'])) {
            $card->setPriceEur($data['priceEur']);
        }
        if (isset($data['priceTix'])) {
            $card->setPriceTix($data['priceTix']);
        }

        return $card;
    }

    /**
     * Cria um card de Pokémon TCG
     */
    public static function createPokemon(array $data): CardPTCG
    {
        $card = new CardPTCG(
            name: $data['name'],
            setId: $data['setId'] ?? $data['set'] ?? '',
            setName: $data['setName'] ?? '',
            number: $data['number'] ?? '',
            rarity: $data['rarity'] ?? 'common'
        );

        // Propriedades opcionais
        if (isset($data['imageUrl'])) {
            $card->setImageUrl($data['imageUrl']);
        }
        if (isset($data['supertype'])) {
            $card->setSupertype($data['supertype']);
        }
        if (isset($data['subtypes'])) {
            $card->setSubtypes($data['subtypes']);
        }
        if (isset($data['hp'])) {
            $card->setHp($data['hp']);
        }
        if (isset($data['types'])) {
            $card->setTypes($data['types']);
        }
        if (isset($data['evolvesFrom'])) {
            $card->setEvolvesFrom($data['evolvesFrom']);
        }
        if (isset($data['attacks'])) {
            $card->setAttacks($data['attacks']);
        }
        if (isset($data['weaknesses'])) {
            $card->setWeaknesses($data['weaknesses']);
        }
        if (isset($data['resistances'])) {
            $card->setResistances($data['resistances']);
        }
        if (isset($data['retreatCost'])) {
            $card->setRetreatCost($data['retreatCost']);
        }
        if (isset($data['artist'])) {
            $card->setArtist($data['artist']);
        }
        if (isset($data['nationalPokedexNumber'])) {
            $card->setNationalPokedexNumber($data['nationalPokedexNumber']);
        }
        if (isset($data['priceUsd'])) {
            $card->setPriceUsd($data['priceUsd']);
        }
        if (isset($data['priceEur'])) {
            $card->setPriceEur($data['priceEur']);
        }

        return $card;
    }

    /**
     * Cria um card de One Piece Card Game
     */
    public static function createOnePiece(array $data): CardOPCG
    {
        $card = new CardOPCG(
            name: $data['name'],
            setCode: $data['setCode'] ?? $data['set'] ?? '',
            setName: $data['setName'] ?? '',
            cardNumber: $data['cardNumber'] ?? $data['number'] ?? '',
            rarity: $data['rarity'] ?? 'common'
        );

        // Propriedades opcionais
        if (isset($data['imageUrl'])) {
            $card->setImageUrl($data['imageUrl']);
        }
        if (isset($data['color'])) {
            $card->setColor($data['color']);
        }
        if (isset($data['category'])) {
            $card->setCategory($data['category']);
        }
        if (isset($data['cost'])) {
            $card->setCost($data['cost']);
        }
        if (isset($data['power'])) {
            $card->setPower($data['power']);
        }
        if (isset($data['counter'])) {
            $card->setCounter($data['counter']);
        }
        if (isset($data['attributes'])) {
            $card->setAttributes($data['attributes']);
        }
        if (isset($data['types'])) {
            $card->setTypes($data['types']);
        }
        if (isset($data['effect'])) {
            $card->setEffect($data['effect']);
        }
        if (isset($data['trigger'])) {
            $card->setTrigger($data['trigger']);
        }
        if (isset($data['artist'])) {
            $card->setArtist($data['artist']);
        }
        if (isset($data['priceUsd'])) {
            $card->setPriceUsd($data['priceUsd']);
        }
        if (isset($data['priceJpy'])) {
            $card->setPriceJpy($data['priceJpy']);
        }

        return $card;
    }

    /**
     * Cria múltiplos cards a partir de um array de dados
     * 
     * @param string $game
     * @param array $dataArray
     * @return Card[]
     */
    public static function createMultiple(string $game, array $dataArray): array
    {
        return array_map(
            fn($data) => self::create($game, $data),
            $dataArray
        );
    }

    /**
     * Cria um card a partir de um CardDTO
     * 
     * @param \App\DTO\CardDTO $dto
     * @return Card
     */
    public static function createFromDTO(\App\DTO\CardDTO $dto): Card
    {
        $data = $dto->toArray();
        
        // Adicionar preços
        if ($dto->prices !== null) {
            $data['priceUsd'] = $dto->prices->usd;
            if ($dto->game === 'mtg') {
                $data['priceEur'] = $dto->prices->usdFoil; // Ajustar conforme necessário
            } elseif ($dto->game === 'pokemon') {
                $data['priceEur'] = $dto->prices->usdFoil;
            } elseif ($dto->game === 'onepiece') {
                $data['priceJpy'] = $dto->prices->usdFoil; // Ajustar conforme necessário
            }
        }

        // Mapear campos específicos por jogo
        if ($dto->game === 'mtg') {
            $data['setCode'] = $dto->set;
            $data['collectorNumber'] = $dto->number;
            $data['setName'] = $dto->setName;
            $data['typeLine'] = $dto->type;
            $data['oracleText'] = $dto->text;
            $data['isFoil'] = $dto->isFoil;
        } elseif ($dto->game === 'pokemon') {
            $data['setId'] = $dto->set;
            $data['number'] = $dto->number;
            $data['setName'] = $dto->setName;
        } elseif ($dto->game === 'onepiece') {
            $data['setCode'] = $dto->set;
            $data['cardNumber'] = $dto->number;
            $data['setName'] = $dto->setName;
        }

        return self::create($dto->game, $data);
    }
}
