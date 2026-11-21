<?php
/**
 * SmashHub AI Engine - Vietnamese N-Gram Text Preprocessor & Tokenizer
 * Language: Pure PHP 7.4+
 */

class Tokenizer {
    private static $compoundWords = [
        'bình thạnh' => 'bình_thạnh',
        'gò vấp' => 'gò_vấp',
        'quận 10' => 'quận_10',
        'quận 1' => 'quận_1',
        'quận 3' => 'quận_3',
        'quận 7' => 'quận_7',
        'tân bình' => 'tân_bình',
        'phú nhuận' => 'phú_nhuận',
        'thủ đức' => 'thủ_đức',
        'chiều nay' => 'chiều_nay',
        'tối nay' => 'tối_nay',
        'sáng mai' => 'sáng_mai',
        'tối mai' => 'tối_mai',
        'ngày mai' => 'ngày_mai',
        'ngày mốt' => 'ngày_mốt',
        'cuối tuần' => 'cuối_tuần',
        'thảm bwf' => 'thảm_bwf',
        'sân trống' => 'sân_trống',
        'đặt sân' => 'đặt_sân',
        'tìm sân' => 'tìm_sân',
        'thuê sân' => 'thuê_sân',
        'chia đôi' => 'chia_đôi',
        'bùng kèo' => 'bùng_kèo',
        'điểm uy tín' => 'điểm_uy_tín',
        'uy tín' => 'uy_tín',
        'bắt cặp' => 'bắt_cặp',
        'ghép kèo' => 'ghép_kèo',
        'tìm đối' => 'tìm_đối',
        'căng cước' => 'căng_cước',
        'chọn vợt' => 'chọn_vợt',
        'mua vợt' => 'mua_vợt',
        'vợt cầu lông' => 'vợt_cầu_lông',
        'công thủ toàn diện' => 'công_thủ_toàn_diện',
        'phát cầu' => 'phát_cầu',
        'giao cầu' => 'giao_cầu',
        'luật thi đấu' => 'luật_thi_đấu',
        'tính điểm' => 'tính_điểm',
        'chạm lưới' => 'chạm_lưới',
        'đổi sân' => 'đổi_sân',
        'giá rẻ' => 'giá_rẻ',
        'sinh viên' => 'sinh_viên',
        'bãi đỗ xe' => 'bãi_đỗ_xe',
        'bãi ô tô' => 'bãi_ô_tô'
    ];

    private static $stopWords = [
        'cho', 'với', 'của', 'và', 'các', 'những', 'cái', 'thì', 'là',
        'mà', 'nhé', 'ạ', 'ơi', 'nè', 'nha', 'ad', 'bot', 'bạn', 'mình',
        'em', 'anh', 'chị', 'tôi', 'hộ', 'giúp'
    ];

    public static function normalize($text) {
        $text = mb_strtolower(trim((string)$text), 'UTF-8');
        // Chuyen ky tu dac biet thanh khoang trang
        $text = preg_replace('/[^\p{L}\p{N}_\s]/u', ' ', $text);
        
        // Ket noi cac cum tu ghep dac thu cau long
        foreach (self::$compoundWords as $phrase => $compound) {
            $text = preg_replace('/\b' . preg_quote($phrase, '/') . '\b/u', $compound, $text);
        }

        // Chuan hoa khoang trang
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    public static function tokenize($text) {
        $normalized = self::normalize($text);
        if ($normalized === '') {
            return [];
        }

        $rawTokens = explode(' ', $normalized);
        $filteredTokens = [];

        foreach ($rawTokens as $tok) {
            $tok = trim($tok);
            if (mb_strlen($tok, 'UTF-8') >= 2 && !in_array($tok, self::$stopWords, true)) {
                $filteredTokens[] = $tok;
            }
        }

        // Tao them Bi-gram de tang do chinh xac ngu canh
        $n = count($filteredTokens);
        $allTokens = $filteredTokens;
        for ($i = 0; $i < $n - 1; $i++) {
            $allTokens[] = $filteredTokens[$i] . '__' . $filteredTokens[$i + 1];
        }

        return $allTokens;
    }
}
