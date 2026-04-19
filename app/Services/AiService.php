<?php

namespace App\Services;

class AiService
{
    private $apiKey;
    private $model;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(string $apiKey, string $model = 'gemini-flash-latest')
    {
        $this->apiKey = trim($apiKey);
        $this->model = trim($model) ?: 'gemini-flash-latest';
        
        if (empty($this->apiKey)) {
            error_log("AiService Warning: Initialized without an API key.");
        }
    }

    /**
     * Generate summary and keywords for document text
     */
    public function generateSummary(string $text): array
    {
        try {
            // Use first 12000 chars for summary
            $summaryText = substr($text, 0, 12000);

            $prompt = "Please analyze the following document and provide:
1. A concise 2-3 paragraph summary (max 300 words)
2. A JSON array of 5-10 key topics/keywords

Format your response as JSON with keys 'summary' and 'keywords' (array of strings).

Document:
" . $summaryText;

            $response = $this->makeRequest($prompt);

            return [
                'summary' => $response['summary'] ?? '',
                'keywords' => $response['keywords'] ?? [],
            ];
        } catch (\Exception $e) {
            // Fallback: Generate basic summary from text
            error_log("AI Service Error: " . $e->getMessage());
            
            return [
                'summary' => 'AI summary generation unavailable. ' . substr($text, 0, 300) . '...',
                'keywords' => ['document', 'pdf', 'analysis'],
            ];
        }
    }

    /**
     * Get AI response to question about document
     */
    public function askQuestion(string $documentText, string $question, array $chatHistory = [], string $style = 'balanced', string $depth = 'standard'): string
    {
        try {
            // Build context from chat history (last 5 turns)
            $historyContext = $this->buildHistoryContext($chatHistory);

            // Split document into chunks
            $chunks = DocumentParser::chunkText($documentText);

            // Use first chunk as primary context
            $documentContext = $chunks[0] ?? $documentText;

            $styleInstruction = "";
            if ($style === 'concise') {
                $styleInstruction = "Be extremely concise. Use bullet points heavily and avoid conversational filler.";
            } elseif ($style === 'detailed') {
                $styleInstruction = "Be highly verbose and detailed. Provide comprehensive explanations, elaborating on related points.";
            } else {
                $styleInstruction = "Provide a balanced, clear response that is easy to read.";
            }

            $depthInstruction = "";
            if ($depth === 'academic') {
                $depthInstruction = "Use a formal, academic tone. Focus on analytical rigor and precise terminology.";
            } elseif ($depth === 'eli5') {
                $depthInstruction = "Explain like I'm 5. Use very simple language, relatable analogies, and completely avoid jargon.";
            } else {
                $depthInstruction = "Use a standard, professional tone suitable for a general audience.";
            }

            $prompt = "You are an AI assistant analyzing a document for a user. Answer questions based on the document context provided.

FORMATTING AND TONE DIRECTIVES:
- " . $styleInstruction . "
- " . $depthInstruction . "

DOCUMENT CONTEXT:
" . $documentContext . "

CHAT HISTORY:
" . $historyContext . "

USER QUESTION:
" . $question . "

Provide an accurate answer based on the document. If the answer is not in the document, say so clearly.";

            return $this->makeRequest($prompt, returnRaw: true);
        } catch (\Exception $e) {
            error_log("AI Q&A Error: " . $e->getMessage());
            $errorMsg = $e->getMessage();
            if (empty($this->apiKey)) {
                return "AI Analyst Error: The Gemini API key is missing. Please check your .env file.";
            }
            return "AI Analyst is temporarily unavailable: " . $errorMsg;
        }
    }

    /**
     * Build context string from chat history
     */
    private function buildHistoryContext(array $history): string
    {
        $context = '';
        foreach ($history as $msg) {
            $role = strtoupper($msg['role']);
            $context .= "$role: " . substr($msg['message'], 0, 200) . "\n";
        }
        return $context ?: "(No previous messages)";
    }

    /**
     * Make API request to Google Gemini
     */
    private function makeRequest(string $prompt, bool $returnRaw = false): mixed
    {
        $url = "{$this->apiUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2000,
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Gemini API error (HTTP $httpCode): $response");
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            throw new \Exception("Gemini API error: " . json_encode($decoded['error']));
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if ($returnRaw) {
            return $text;
        }

        // Parse JSON response
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            return $json ?? ['summary' => $text, 'keywords' => []];
        }

        return ['summary' => $text, 'keywords' => []];
    }
}
