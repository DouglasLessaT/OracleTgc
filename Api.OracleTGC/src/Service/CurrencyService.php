<?php

namespace App\Service;

use App\DTO\ExchangeRatesDTO;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyService
{
    private const COINGECKO_API = 'https://api.coingecko.com/api/v3';
    private const EXCHANGE_RATE_API = 'https://api.exchangerate-api.com/v4/latest/USD';
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function getExchangeRates(): ExchangeRatesDTO
    {
        $cacheKey = 'exchange_rates';
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            return $cached->get();
        }

        try {
            // Get Bitcoin price in USD
            $btcResponse = $this->httpClient->request('GET', self::COINGECKO_API . '/simple/price', [
                'query' => [
                    'ids' => 'bitcoin',
                    'vs_currencies' => 'usd',
                ],
                'timeout' => 5,
            ]);

            $btcData = $btcResponse->toArray();
            $btcPriceInUsd = $btcData['bitcoin']['usd'] ?? 43000;

            // Get USD to BRL rate
            $exchangeResponse = $this->httpClient->request('GET', self::EXCHANGE_RATE_API, [
                'timeout' => 5,
            ]);

            $exchangeData = $exchangeResponse->toArray();
            $usdToBrl = $exchangeData['rates']['BRL'] ?? 5.0;

            $rates = new ExchangeRatesDTO(
                usdToBtc: 1 / $btcPriceInUsd,
                usdToBrl: $usdToBrl,
                btcToUsd: $btcPriceInUsd,
                btcToBrl: $btcPriceInUsd * $usdToBrl,
            );

            $cached->set($rates);
            $cached->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cached);

            return $rates;
        } catch (\Exception $e) {
            // Return fallback values
            return new ExchangeRatesDTO(
                usdToBtc: 0.000023,
                usdToBrl: 5.0,
                btcToUsd: 43000,
                btcToBrl: 215000,
            );
        }
    }

    public function convertUsdToAllCurrencies(float $usdPrice, ExchangeRatesDTO $rates): array
    {
        return [
            'usd' => $usdPrice,
            'btc' => $usdPrice * $rates->usdToBtc,
            'brl' => $usdPrice * $rates->usdToBrl,
        ];
    }
}

