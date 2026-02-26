<?php

namespace App\Controller\Api;

use App\Service\OCRService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ocr', name: 'api_ocr_')]
class OCRController extends AbstractController
{
    public function __construct(
        private OCRService $ocrService,
    ) {
    }

    #[Route('/extract', name: 'extract', methods: ['POST'])]
    public function extractCardInfo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';

        if (empty($text)) {
            return $this->json(['error' => 'Text parameter is required'], 400);
        }

        $cardInfo = $this->ocrService->extractCardInfo($text);

        return $this->json($cardInfo);
    }
}

