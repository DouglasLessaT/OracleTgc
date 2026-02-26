<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Serviço para baixar e armazenar imagens de cartas em storage local.
 * Permite que a API funcione de forma independente das APIs externas após o sync.
 */
class CardImageStorageService
{
    /** Caminho relativo à raiz web (public) para servir as imagens */
    public const WEB_PATH_PREFIX = '/storage/cards';

    public function __construct(
        private string $projectDir,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * Retorna o diretório absoluto onde as imagens são salvas (em public para serem servidas).
     */
    public function getStorageDir(): string
    {
        $dir = $this->projectDir . '/public/storage/cards';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Baixa a imagem de uma URL e salva em storage/cards/{game}/{setCode}/{number}.{ext}.
     * Retorna o path web para usar em imageUrl (ex: /storage/cards/mtg/NEO/123.jpg).
     */
    public function downloadAndSave(
        string $imageUrl,
        string $game,
        string $setCode,
        string $number
    ): ?string {
        if (empty($imageUrl)) {
            return null;
        }

        $ext = $this->guessExtension($imageUrl);
        $baseDir = $this->getStorageDir() . '/' . strtolower($game) . '/' . $this->sanitizeDirName($setCode);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $filename = $this->sanitizeFileName($number) . '.' . $ext;
        $filepath = $baseDir . '/' . $filename;

        try {
            $response = $this->httpClient->request('GET', $imageUrl, [
                'timeout' => 15,
            ]);
            $content = $response->getContent();
            if (strlen($content) === 0) {
                return null;
            }
            file_put_contents($filepath, $content);
        } catch (\Throwable $e) {
            return null;
        }

        return self::WEB_PATH_PREFIX . '/' . strtolower($game) . '/' . $this->sanitizeDirName($setCode) . '/' . $filename;
    }

    /**
     * Retorna o path web onde a imagem deve estar (sem baixar).
     * Útil para verificar se já existe.
     */
    public function getLocalPath(string $game, string $setCode, string $number, string $ext = 'jpg'): string
    {
        return self::WEB_PATH_PREFIX . '/' . strtolower($game) . '/' . $this->sanitizeDirName($setCode) . '/' . $this->sanitizeFileName($number) . '.' . $ext;
    }

    /**
     * Verifica se a imagem já existe no storage local.
     */
    public function hasLocalImage(string $game, string $setCode, string $number): bool
    {
        $baseDir = $this->getStorageDir() . '/' . strtolower($game) . '/' . $this->sanitizeDirName($setCode);
        $sanitized = $this->sanitizeFileName($number);
        foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
            if (is_file($baseDir . '/' . $sanitized . $ext)) {
                return true;
            }
        }
        return false;
    }

    private function guessExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path && preg_match('/\.(jpe?g|png|webp)$/i', $path, $m)) {
            return strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
        }
        return 'jpg';
    }

    private function sanitizeDirName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?: 'unknown';
    }

    private function sanitizeFileName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?: 'unknown';
    }
}
