<?php

namespace App\Controller\Api;

use App\Service\CurrencyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/currency', name: 'api_currency_')]
class CurrencyController extends AbstractController
{
    public function __construct(
        private CurrencyService $currencyService,
    ) {
    }

    #[Route('/rates', name: 'rates', methods: ['GET'])]
    public function getExchangeRates(): JsonResponse
    {
        $rates = $this->currencyService->getExchangeRates();
        return $this->json($rates->toArray());
    }

    #[Route('/convert', name: 'convert', methods: ['GET'])]
    public function convert(float $usd): JsonResponse
    {
        $rates = $this->currencyService->getExchangeRates();
        $converted = $this->currencyService->convertUsdToAllCurrencies($usd, $rates);
        return $this->json($converted);
    }
}

