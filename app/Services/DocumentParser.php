<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;

class DocumentParser
{
    /**
     * Extract text from PDF or DOCX file
     */
    public static function extractText(string $filePath, string $fileType): string
    {
        if ($fileType === 'docx') {
            return self::extractDocxText($filePath);
        } elseif ($fileType === 'pdf') {
            return self::extractPdfText($filePath);
        }

        return '';
    }

    /**
     * Extract text from DOCX using PhpWord
     */
    private static function extractDocxText(string $filePath): string
    {
        try {
            // Priority 1: Use PhpWord for high-fidelity extraction
            $phpWord = IOFactory::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text .= self::getElementText($element) . "\n";
                }
            }

            $extracted = trim($text);
            if (!empty($extracted)) {
                return $extracted;
            }
            
            throw new \Exception("PhpWord extracted no text");

        } catch (\Throwable $e) {
            // Priority 2: Native XML Fallback (Ultra-reliable, minimal dependencies)
            return self::extractDocxTextNative($filePath);
        }
    }

    /**
     * Native fallback to extract text from DOCX by parsing word/document.xml directly.
     * This ensures text can be extracted even if PhpWord or its extensions fail.
     */
    private static function extractDocxTextNative(string $filePath): string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    $data = $zip->getFromIndex($index);
                    $zip->close();

                    // Strip XML tags and unescape entities
                    $text = strip_tags($data, '<w:p><w:r><w:t>');
                    $text = preg_replace('/<w:p[^>]*>/', "\n", $text);
                    $text = strip_tags($text);
                    
                    return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $zip->close();
            }
            throw new \Exception("Native ZIP extraction failed");
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse DOCX: " . $e->getMessage());
        }
    }


    /**
     * Recursively extract text from elements
     */
    private static function getElementText($element): string
    {
        $text = '';

        try {
            if ($element instanceof TextRun) {
                foreach ($element->getElements() as $childElement) {
                    if (method_exists($childElement, 'getText')) {
                        $text .= $childElement->getText();
                    }
                }
            } elseif (method_exists($element, 'getText')) {
                $text = $element->getText();
            } elseif (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    $text .= self::getElementText($childElement);
                }
            }
        } catch (\Exception $e) {
            // Silently skip elements that fail
        }

        return $text;
    }

    /**
     * Extract text from PDF using smalot/pdfparser
     */
    private static function extractPdfText(string $filePath): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);

            $text = '';
            foreach ($pdf->getPages() as $page) {
                $text .= $page->getText() . "\n";
            }

            return trim($text);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse PDF: " . $e->getMessage());
        }
    }

    /**
     * Split text into chunks for AI processing
     */
    public static function chunkText(string $text, int $chunkSize = 8000, int $overlapSize = 500): array
    {
        $chunks = [];
        $length = strlen($text);

        if ($length <= $chunkSize) {
            return [$text];
        }

        $offset = 0;
        while ($offset < $length) {
            $chunks[] = substr($text, $offset, $chunkSize);
            $offset += ($chunkSize - $overlapSize);
        }

        return $chunks;
    }

    /**
     * Get first N characters for summary
     */
    public static function getSummaryText(string $text, int $maxChars = 12000): string
    {
        return substr($text, 0, $maxChars);
    }
}
