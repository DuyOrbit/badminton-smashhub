<?php
/**
 * SmashHub AI Engine - Multinomial Naive Bayes Intent Classifier
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/TfIdfVectorizer.php';

class NaiveBayesClassifier {
    private $vectorizer;
    private $classes = [];
    private $classPriors = [];
    private $featureProbabilities = [];
    private $alpha = 1.0; // Laplace smoothing parameter

    public function __construct($alpha = 1.0) {
        $this->alpha = (float)$alpha;
        $this->vectorizer = new TfIdfVectorizer();
    }

    public function train($samples) {
        // samples: array of ['text' => '...', 'intent' => '...']
        $documents = [];
        $labels = [];
        $classDocCounts = [];

        foreach ($samples as $s) {
            $documents[] = $s['text'];
            $intent = $s['intent'];
            $labels[] = $intent;
            $classDocCounts[$intent] = ($classDocCounts[$intent] ?? 0) + 1;
        }

        $this->classes = array_values(array_unique($labels));
        sort($this->classes);

        // 1. Fit TF-IDF Vectorizer
        $this->vectorizer->fit($documents);
        $vocab = $this->vectorizer->getVocabulary();
        $vocabSize = count($vocab);

        // 2. Tinh xac suat tien nghiem P(c) - Log Class Priors
        $totalDocs = count($documents);
        $this->classPriors = [];
        foreach ($this->classes as $c) {
            $count = $classDocCounts[$c] ?? 0;
            $this->classPriors[$c] = log(($count + $this->alpha) / ($totalDocs + $this->alpha * count($this->classes)));
        }

        // 3. Tinh tong trong so feature cua tung lop voi Laplace smoothing
        $featureWeightsPerClass = [];
        $totalWeightPerClass = [];

        foreach ($this->classes as $c) {
            $featureWeightsPerClass[$c] = [];
            $totalWeightPerClass[$c] = 0.0;
        }

        foreach ($samples as $idx => $s) {
            $c = $s['intent'];
            $vec = $this->vectorizer->transform($s['text']);
            foreach ($vec as $term => $w) {
                $featureWeightsPerClass[$c][$term] = ($featureWeightsPerClass[$c][$term] ?? 0.0) + $w;
                $totalWeightPerClass[$c] += $w;
            }
        }

        // 4. Log feature probabilities: ln P(w|c) = ln((weight(w, c) + alpha) / (totalWeight(c) + alpha * |V|))
        $this->featureProbabilities = [];
        foreach ($this->classes as $c) {
            $this->featureProbabilities[$c] = [];
            $denom = $totalWeightPerClass[$c] + ($this->alpha * $vocabSize);
            foreach ($vocab as $term => $i) {
                $num = ($featureWeightsPerClass[$c][$term] ?? 0.0) + $this->alpha;
                $this->featureProbabilities[$c][$term] = log($num / $denom);
            }
            // Luu them default log prob cho tu khong co trong lop
            $this->featureProbabilities[$c]['__UNSEEN__'] = log($this->alpha / $denom);
        }
    }

    public function predict($text) {
        $scores = $this->predictLogProbabilities($text);
        if (empty($scores)) {
            return [
                'intent'     => 'find_pitch',
                'confidence' => 0.5,
                'scores'     => []
            ];
        }

        arsort($scores);
        $bestClass = array_key_first($scores);

        // Softmax conversion for calibrated confidence score
        $maxScore = max($scores);
        $sumExp = 0.0;
        $expScores = [];
        foreach ($scores as $c => $score) {
            $val = exp($score - $maxScore);
            $expScores[$c] = $val;
            $sumExp += $val;
        }

        $probabilities = [];
        foreach ($expScores as $c => $val) {
            $probabilities[$c] = round($val / $sumExp, 4);
        }

        return [
            'intent'        => $bestClass,
            'confidence'    => $probabilities[$bestClass],
            'probabilities' => $probabilities
        ];
    }

    private function predictLogProbabilities($text) {
        $tokens = Tokenizer::tokenize($text);
        if (empty($tokens)) {
            return [];
        }

        $vocab = $this->vectorizer->getVocabulary();
        $scores = [];

        foreach ($this->classes as $c) {
            $logProb = $this->classPriors[$c] ?? 0.0;
            $unseenProb = $this->featureProbabilities[$c]['__UNSEEN__'] ?? -10.0;

            foreach ($tokens as $tok) {
                if (isset($vocab[$tok])) {
                    if (isset($this->featureProbabilities[$c][$tok])) {
                        $logProb += $this->featureProbabilities[$c][$tok];
                    } else {
                        $logProb += $unseenProb;
                    }
                }
            }
            $scores[$c] = $logProb;
        }

        return $scores;
    }

    public function exportModel() {
        return [
            'version'               => '1.0.0',
            'algorithm'             => 'Multinomial Naive Bayes with TF-IDF',
            'created_at'            => date('Y-m-d H:i:s'),
            'classes'               => $this->classes,
            'class_priors'          => $this->classPriors,
            'feature_probabilities' => $this->featureProbabilities,
            'vectorizer'            => $this->vectorizer->exportData()
        ];
    }

    public function loadModel($modelData) {
        $this->classes = $modelData['classes'] ?? [];
        $this->classPriors = $modelData['class_priors'] ?? [];
        $this->featureProbabilities = $modelData['feature_probabilities'] ?? [];
        $this->vectorizer->loadData($modelData['vectorizer'] ?? []);
    }
}
