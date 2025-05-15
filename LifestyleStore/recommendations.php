<?php
class ProductRecommender {
    private $con;
    private $items;
    private $tfidf;

    public function __construct($con) {
        $this->con = $con;
        $this->loadItems();
        $this->calculateTFIDF();
    }

    private function loadItems() {
        $query = "SELECT * FROM items";
        $result = mysqli_query($this->con, $query);
        $this->items = array();
        while($row = mysqli_fetch_assoc($result)) {
            $this->items[$row['id']] = $row;
        }
    }

    private function calculateTFIDF() {
        $this->tfidf = array();
        $documents = array();
        
        // Prepare documents by combining metadata
        foreach($this->items as $id => $item) {
            $documents[$id] = strtolower($item['category'] . ' ' . 
                                       $item['brand'] . ' ' . 
                                       $item['material'] . ' ' . 
                                       $item['color'] . ' ' . 
                                       $item['name']);
        }

        // Calculate term frequencies
        $termFreq = array();
        foreach($documents as $id => $doc) {
            $terms = explode(' ', $doc);
            $termFreq[$id] = array_count_values($terms);
        }

        // Calculate document frequencies
        $docFreq = array();
        foreach($termFreq as $id => $terms) {
            foreach($terms as $term => $freq) {
                if(!isset($docFreq[$term])) {
                    $docFreq[$term] = 0;
                }
                $docFreq[$term]++;
            }
        }

        // Calculate TF-IDF
        $N = count($documents);
        foreach($termFreq as $id => $terms) {
            $this->tfidf[$id] = array();
            foreach($terms as $term => $freq) {
                $tf = $freq / count($terms);
                $idf = log($N / $docFreq[$term]);
                $this->tfidf[$id][$term] = $tf * $idf;
            }
        }
    }

    private function cosineSimilarity($vec1, $vec2) {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        // Get all unique terms
        $terms = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));

        foreach($terms as $term) {
            $val1 = isset($vec1[$term]) ? $vec1[$term] : 0;
            $val2 = isset($vec2[$term]) ? $vec2[$term] : 0;
            
            $dotProduct += $val1 * $val2;
            $norm1 += $val1 * $val1;
            $norm2 += $val2 * $val2;
        }

        if($norm1 == 0 || $norm2 == 0) return 0;
        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }

    public function getRecommendations($itemId, $limit = 4) {
        if(!isset($this->tfidf[$itemId])) {
            return array();
        }

        $scores = array();
        foreach($this->tfidf as $id => $vec) {
            if($id != $itemId) {
                $score = $this->cosineSimilarity($this->tfidf[$itemId], $vec);
                $scores[$id] = $score;
            }
        }

        arsort($scores);
        $recommendations = array();
        $count = 0;
        foreach($scores as $id => $score) {
            if($count >= $limit) break;
            $recommendations[] = $this->items[$id];
            $count++;
        }

        return $recommendations;
    }
}
?> 