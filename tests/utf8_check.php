<?php
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    @sapi_windows_cp_set(65001);
}

require_once __DIR__ . '/../config/database.php';

echo "=== SMASHHUB UTF-8 & MOJIBAKE VERIFICATION TEST ===\n\n";

$pass = true;
$errors = [];

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Khong the ket noi CSDL.");
    }
    echo "[PASS] 1. Ket noi co so du lieu thanh cong.\n";
} catch (Exception $e) {
    echo "[FAIL] 1. Ket noi co so du lieu that bai: " . $e->getMessage() . "\n";
    exit(1);
}

$mojibakePatterns = ['Ã', 'á»', 'Ä‘', 'Ã¡', 'Ã¢', 'Ã©', 'Ã¨', 'Ã³', 'Ã´', 'Ãº', 'ß╗', 'Γ', '├'];
function hasMojibake($text, $patterns) {
    if (!is_string($text)) {
        return false;
    }
    foreach ($patterns as $pattern) {
        if (strpos($text, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

// 2. Query users
echo "\n2. Kiem tra tieng Viet trong bang 'users':\n";
$stmtUsers = $db->query("SELECT id, username, name FROM users WHERE name IS NOT NULL AND name != '' LIMIT 5");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "[WARN] Khong tim thay ban ghi user nao co name.\n";
} else {
    foreach ($users as $u) {
        $validUtf8 = mb_check_encoding($u['name'], 'UTF-8');
        $matchedPattern = null;
        foreach ($mojibakePatterns as $pat) {
            if (strpos($u['name'], $pat) !== false) {
                $matchedPattern = $pat;
                break;
            }
        }
        if ($validUtf8 && $matchedPattern === null) {
            echo "  [PASS] User #{$u['id']} ({$u['username']}): '{$u['name']}' (Hop le UTF-8)\n";
        } else {
            $pass = false;
            $reason = !$validUtf8 ? "Khong phai UTF-8" : "Chua mau mojibake: " . bin2hex($matchedPattern);
            $msg = "User #{$u['id']} ({$u['username']}): '{$u['name']}' (hex: " . bin2hex($u['name']) . ") bi loi: {$reason}";
            $errors[] = $msg;
            echo "  [FAIL] {$msg}\n";
        }
    }
}

// 3. Query pitches
echo "\n3. Kiem tra tieng Viet trong bang 'pitches':\n";
$stmtPitches = $db->query("SELECT id, name, location, district FROM pitches LIMIT 5");
$pitches = $stmtPitches->fetchAll(PDO::FETCH_ASSOC);

if (empty($pitches)) {
    echo "[WARN] Khong tim thay ban ghi pitch nao.\n";
} else {
    foreach ($pitches as $p) {
        $fields = ['name' => $p['name'], 'location' => $p['location'], 'district' => $p['district']];
        $pitchOk = true;
        foreach ($fields as $col => $val) {
            if ($val !== null && $val !== '') {
                $matchedPattern = null;
                foreach ($mojibakePatterns as $pat) {
                    if (strpos($val, $pat) !== false) {
                        $matchedPattern = $pat;
                        break;
                    }
                }
                if (!mb_check_encoding($val, 'UTF-8') || $matchedPattern !== null) {
                    $pitchOk = false;
                    $pass = false;
                    $reason = !mb_check_encoding($val, 'UTF-8') ? "Khong phai UTF-8" : "Chua mau mojibake: " . bin2hex($matchedPattern);
                    $msg = "Pitch #{$p['id']} cot '{$col}': '{$val}' (hex: " . bin2hex($val) . ") bi loi: {$reason}";
                    $errors[] = $msg;
                    echo "  [FAIL] {$msg}\n";
                }
            }
        }
        if ($pitchOk) {
            echo "  [PASS] Pitch #{$p['id']}: '{$p['name']}' | '{$p['district']}' (Hop le UTF-8)\n";
        }
    }
}

// 4. Transaction insert & read back "Nguyễn Duy"
echo "\n4. Kiem tra ghi va doc chuoi 'Nguyễn Duy' qua Transaction:\n";
try {
    $db->beginTransaction();
    $testName = "Nguyễn Duy";
    $testUsername = "test_utf8_" . time();
    $testPhone = "0987" . rand(100000, 999999);

    $stmtInsert = $db->prepare("INSERT INTO users (username, password, name, phone, role) VALUES (?, 'testpass', ?, ?, 'user')");
    $stmtInsert->execute([$testUsername, $testName, $testPhone]);
    $insertedId = (int)$db->lastInsertId();

    $stmtSelect = $db->prepare("SELECT name FROM users WHERE id = ?");
    $stmtSelect->execute([$insertedId]);
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);
    $readBackName = $row['name'] ?? '';

    $db->rollBack();

    if ($readBackName === $testName) {
        echo "  [PASS] Ghi '{$testName}', doc lai '{$readBackName}' khop 100% tung byte.\n";
    } else {
        $pass = false;
        $msg = "Ghi '{$testName}' nhung doc lai duoc '{$readBackName}'.";
        $errors[] = $msg;
        echo "  [FAIL] {$msg}\n";
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $pass = false;
    $msg = "Loi giao tac kiem tra ghi/doc: " . $e->getMessage();
    $errors[] = $msg;
    echo "  [FAIL] {$msg}\n";
}

echo "\n==============================================================\n";
if ($pass) {
    echo "KET QUA: [ALL CHECKS PASSED] Co so du lieu va du an hoan toan tuong thich UTF-8 chuan.\n";
    exit(0);
} else {
    echo "KET QUA: [FAILED] Co " . count($errors) . " loi duoc phat hien:\n";
    foreach ($errors as $err) {
        echo " - {$err}\n";
    }
    exit(1);
}
