<?php

namespace App\Service;

class OCRService
{
    public function extractCardInfo(string $text): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)), fn($line) => !empty($line));
        
        // First non-empty line is usually the card name
        $possibleName = $lines[0] ?? '';
        
        // Look for set code pattern (e.g., "XY12", "SV1", "NEO")
        $setPattern = '/\b([A-Z]{2,4}\d{0,3})\b/';
        preg_match($setPattern, $text, $setMatches);
        $possibleSet = $setMatches[1] ?? '';
        
        // Look for card number pattern (e.g., "123/200", "45/102")
        $numberPattern = '/(\d{1,3})\s*[\/\\]\s*(\d{1,3})/';
        preg_match($numberPattern, $text, $numberMatches);
        $possibleNumber = $numberMatches[1] ?? '';
        
        return [
            'possibleName' => $this->cleanCardName($possibleName),
            'possibleSet' => $possibleSet,
            'possibleNumber' => $possibleNumber,
        ];
    }

    private function cleanCardName(string $name): string
    {
        // Remove common OCR artifacts and clean up the name
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s\'-]/', '', $name)));
    }
}

