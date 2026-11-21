<?php
/**
 * SmashHub AI Engine - TF-IDF (Term Frequency - Inverse Document Frequency) Vectorizer
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/Tokenizer.php';

class TfIdfVectorizer {
    private $vocabulary = [];
    private $idf = [];
    private $documentCount = 0;

    public function fit($documents) {
        $this->documentCount = count($documents);
        $docFreq = [];
        $vocabSet = [];

        foreach ($documents as $doc) {
            $tokens = Tokenizer::tokenize($doc);
            $uniqueTokens = array_unique($tokens);

            foreach ($uniqueTokens as $tok) {
                $vocabSet[$tok] = true;
                if (!isset($docFreq[$tok])) {
                    $docFreq[$tok] = 0;
                }
                $docFreq[$tok]++;
            }
        }

        // Sap xep vocabulary co dinh
        $vocabKeys = array_keys($vocabSet);
        sort($vocabKeys);
        $this->vocabulary = array_flip($vocabKeys);

        // Tinh toan IDF: idf(t) = ln((1 + N) / (1 + df(t))) + 1 (Smooth IDF)
        $this->idf = [];
        foreach ($this->vocabulary as $term => $idx) {
            $df = isset($docFreq[$term]) ? $docFreq[$term] : 0;
            $this->idf[$term] = log((1.0 + $this->documentCount) / (1.0 + $df)) + 1.0;
        }
    }

    public function transform($text) {
        $tokens = Tokenizer::tokenize($text);
        if (empty($tokens)) {
            return [];
        }

        // Tinh TF (Term Frequency)
        $tf = [];
        $totalTokens = count($tokens);
        foreach ($tokens as $tok) {
            if (isset($this->vocabulary[$tok])) {
                $tf[$tok] = ($tf[$tok] ?? 0) + 1;
            }
        }

        // Vector hoa ket hop TF * IDF
        $vector = [];
        $normSq = 0.0;
        foreach ($tf as $tok => $count) {
            $val = ($count / $totalTokens) * $this->idf[$tok];
            $vector[$tok] = $val;
            $normSq += $val * $val;
        }

        // L2 Normalization
        if ($normSq > 0) {
            $norm = sqrt($normSq);
            foreach ($vector as $tok => $val) {
                $vector[$tok] = $val / $norm;
            }
        }

        return $vector;
    }

    public function getVocabulary() {
        return $this->vocabulary;
    }

    public function getIdf() {
        return $this->idf;
    }

    public function exportData() {
        return [
            'document_count' => $this->documentCount,
            'vocabulary'     => $this->vocabulary,
            'idf'            => $this->idf
        ];
    }

    public function loadData($data) {
        $this->documentCount = (int)($data['document_count'] ?? 0);
        $this->vocabulary = $data['vocabulary'] ?? [];
        $this->idf = $data['idf'] ?? [];
    }
}

