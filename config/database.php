<?php
// Database Configuration
$getEnvVal = function($names, $default = '') {
    if (!is_array($names)) $names = [$names];
    foreach ($names as $key) {
        if (!empty($_ENV[$key])) return trim($_ENV[$key]);
        if (!empty($_SERVER[$key])) return trim($_SERVER[$key]);
        $val = getenv($key);
        if ($val !== false && $val !== '') return trim($val);
    }
    return $default;
};

$dbUrl = $getEnvVal(['DATABASE_URL', 'MYSQL_URL']);
$parsedUrl = $dbUrl ? parse_url($dbUrl) : [];

$defaultHost = $parsedUrl['host'] ?? $getEnvVal(['DB_HOST', 'MYSQL_HOST'], '127.0.0.1');
$defaultPort = $parsedUrl['port'] ?? $getEnvVal(['DB_PORT', 'MYSQL_PORT'], '3306');
$defaultUser = $parsedUrl['user'] ?? $getEnvVal(['DB_USER', 'DB_USERNAME', 'MYSQL_USER'], 'root');
$defaultPass = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : $getEnvVal(['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQL_PWD'], '');
$defaultName = !empty($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : $getEnvVal(['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE'], 'badminton_booking');

define('DB_HOST', $defaultHost);
define('DB_PORT', $defaultPort);
define('DB_USER', $defaultUser);
define('DB_PASS', $defaultPass);
define('DB_NAME', $defaultName);

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $error;

    public function __construct() {
        // Set DSN with port & charset
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );

        // If connecting to remote cloud host (not localhost), configure SSL/TLS encryption
        // (Mandatory for TiDB Cloud Serverless, AWS RDS, Aiven, etc.)
        if ($this->host !== '127.0.0.1' && $this->host !== 'localhost') {
            $caPaths = [
                getenv('MYSQL_ATTR_SSL_CA'),
                __DIR__ . '/isrgrootx1.pem',          // Bundled Let's Encrypt ISRG Root X1 (TiDB Cloud)
                '/etc/ssl/certs/ca-certificates.crt', // Standard Debian / Ubuntu / Render
                '/etc/pki/tls/certs/ca-bundle.crt',   // RedHat / CentOS
                '/etc/ssl/ca-bundle.pem',
                '/etc/ssl/cert.pem'
            ];
            $sslConfigured = false;
            foreach ($caPaths as $path) {
                if (!empty($path) && file_exists($path)) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = $path;
                    $sslConfigured = true;
                    break;
                }
            }
            if (!$sslConfigured && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            }
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            // Auto initialize database schema & tables if they do not exist yet
            $this->autoInitializeIfMissing();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            
            // Helpful Diagnostic UI for Cloud Deployments (e.g. Render, Railway, VPS)
            $isRender = !empty(getenv('RENDER')) || !empty(getenv('RENDER_SERVICE_ID'));
            $isLocalhost = ($this->host === '127.0.0.1' || $this->host === 'localhost');
            
            if (strpos($this->error, 'Unknown database') !== false) {
                die("
                <div style='max-width:650px; margin:50px auto; padding:30px; font-family:system-ui, sans-serif; background:#0b1116; color:#fff; border-radius:16px; border:1px solid #334155; line-height:1.6;'>
                    <h2 style='color:#f43f5e; margin-top:0;'>⚠️ Chưa tìm thấy Database '{$this->dbname}'</h2>
                    <p>Hệ thống đã kết nối được tới máy chủ MySQL <code>{$this->host}</code>, nhưng cơ sở dữ liệu <code>{$this->dbname}</code> chưa được tạo hoặc chưa import dữ liệu.</p>
                    <p><strong>Cách khắc phục:</strong> Vui lòng tạo database <code>{$this->dbname}</code> và import file <code>badminton_booking.sql</code> vào máy chủ.</p>
                </div>");
            } elseif (strpos($this->error, '2002') !== false || strpos($this->error, 'Connection refused') !== false) {
                die("
                <div style='max-width:700px; margin:40px auto; padding:30px; font-family:system-ui, -apple-system, sans-serif; background:#0b1116; color:#e2e8f0; border-radius:20px; border:1px solid #334155; line-height:1.7; box-shadow:0 20px 40px rgba(0,0,0,0.5);'>
                    <div style='display:flex; align-items:center; gap:12px; margin-bottom:16px;'>
                        <span style='font-size:28px;'>🏸</span>
                        <h2 style='color:#f43f5e; margin:0; font-size:22px;'>Lỗi Kết Nối CSDL (Connection Refused - Code 2002)</h2>
                    </div>
                    <p style='color:#cbd5e1; font-size:14px;'>Hệ thống không thể kết nối tới máy chủ MySQL tại <code>{$this->host}:{$this->port}</code>.</p>
                    <div style='background:#1e293b; padding:18px; border-radius:12px; border-left:4px solid #38bdf8; margin:20px 0; font-size:13px;'>
                        <strong style='color:#38bdf8; display:block; margin-bottom:8px;'>💡 Nguyên nhân khi chạy trên Render:</strong>
                        Render Web Service là một container chạy độc lập chỉ có mã nguồn PHP, <strong>không có sẵn dịch vụ MySQL chạy ngầm ở 127.0.0.1 (localhost)</strong>.
                    </div>
                    <div style='background:#0f172a; padding:18px; border-radius:12px; border:1px solid #1e293b; margin:20px 0; font-size:13px;'>
                        <strong style='color:#a3e635; display:block; margin-bottom:8px;'>🚀 Cách giải quyết:</strong>
                        <ol style='margin:0; padding-left:20px;'>
                            <li>Tạo 1 cơ sở dữ liệu MySQL trên Cloud miễn phí (Khuyên dùng <strong><a href='https://tidbcloud.com' target='_blank' style='color:#38bdf8;'>TiDB Cloud</a></strong> hoặc <strong><a href='https://aiven.io' target='_blank' style='color:#38bdf8;'>Aiven</a></strong>).</li>
                            <li>Import file <code>badminton_booking.sql</code> vào database cloud đó.</li>
                            <li>Vào <strong>Render Dashboard &rarr; Web Service của bạn &rarr; tab Environment</strong> và thêm các biến môi trường:
                                <ul style='margin-top:6px; font-family:monospace; color:#cbd5e1;'>
                                    <li><strong>DB_HOST</strong>: &lt;host của cloud mysql&gt;</li>
                                    <li><strong>DB_PORT</strong>: &lt;cổng port&gt; (ví dụ 4000 hoặc 3306)</li>
                                    <li><strong>DB_USER</strong>: &lt;username&gt;</li>
                                    <li><strong>DB_PASS</strong>: &lt;password&gt;</li>
                                    <li><strong>DB_NAME</strong>: &lt;tên database&gt;</li>
                                </ul>
                                <em>Hoặc thêm 1 biến duy nhất: <strong>DATABASE_URL</strong></em>
                            </li>
                        </ol>
                    </div>
                    <p style='font-size:12px; color:#64748b; margin-top:20px;'>Chi tiết lỗi kỹ thuật: <code>" . htmlspecialchars($this->error) . "</code></p>
                </div>");
            } elseif (strpos($this->error, '1045') !== false || strpos($this->error, 'Access denied') !== false) {
                $noPass = (strpos($this->error, 'using password: NO') !== false);
                $passHelp = $noPass 
                    ? "Biến <code>DB_PASS</code> (hoặc <code>DB_PASSWORD</code>) trên Render đang <strong>để trống hoặc chưa được lưu</strong>."
                    : "Mật khẩu cho tài khoản <code>{$this->user}</code> không chính xác. Hãy kiểm tra lại mật khẩu trên TiDB Cloud.";
                die("
                <div style='max-width:650px; margin:50px auto; padding:30px; font-family:system-ui, sans-serif; background:#0b1116; color:#fff; border-radius:16px; border:1px solid #334155; line-height:1.6;'>
                    <h2 style='color:#f43f5e; margin-top:0;'>🔑 Chưa có Mật khẩu CSDL (Code 1045)</h2>
                    <p style='color:#cbd5e1;'>{$passHelp}</p>
                    <div style='background:#1e293b; padding:18px; border-radius:12px; margin:18px 0; font-size:14px;'>
                        <strong style='color:#38bdf8;'>🛠️ Cách khắc phục:</strong>
                        <ol style='margin:8px 0 0 20px; line-height:1.7;'>
                            <li>Vào <strong>Render &rarr; tab Environment</strong>.</li>
                            <li>Kiểm tra xem đã có biến <strong>DB_PASS</strong> (hoặc <strong>DB_PASSWORD</strong>) chưa.</li>
                            <li>Nhập mật khẩu TiDB Cloud vào ô <strong>Value</strong>.</li>
                            <li>Bấm <strong>Save Changes</strong>.</li>
                        </ol>
                    </div>
                </div>");
            } else {
                die("
                <div style='max-width:650px; margin:50px auto; padding:30px; font-family:system-ui, sans-serif; background:#0b1116; color:#fff; border-radius:16px; border:1px solid #334155;'>
                    <h3 style='color:#f43f5e;'>Lỗi Kết Nối Cơ Sở Dữ Liệu</h3>
                    <p>Chi tiết: <code>" . htmlspecialchars($this->error) . "</code></p>
                </div>");
            }
        }
    }

    /**
     * Automatically populate database tables and initial data if they do not exist
     */
    private function autoInitializeIfMissing() {
        if (!$this->dbh) return;
        try {
            $stmt = $this->dbh->query("SHOW TABLES LIKE 'pitches'");
            if ($stmt && $stmt->rowCount() === 0) {
                $sqlFiles = [
                    __DIR__ . '/../tidb_import.sql',
                    __DIR__ . '/../badminton_booking.sql'
                ];
                $sqlContent = null;
                foreach ($sqlFiles as $file) {
                    if (file_exists($file)) {
                        $sqlContent = file_get_contents($file);
                        break;
                    }
                }
                if ($sqlContent) {
                    $cleaned = preg_replace('/^--.*$/m', '', $sqlContent);
                    $statements = array_filter(array_map('trim', explode(';', $cleaned)));
                    $this->dbh->exec("SET FOREIGN_KEY_CHECKS = 0;");
                    foreach ($statements as $s) {
                        if (!empty($s)) {
                            try {
                                $this->dbh->exec($s);
                            } catch (\PDOException $ex) {
                                // continue executing other statements
                            }
                        }
                    }
                    $this->dbh->exec("SET FOREIGN_KEY_CHECKS = 1;");
                }
            }
            // Auto-migrate split payment columns in match_tickets if missing
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash'");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN payment_status ENUM('unpaid','paid') DEFAULT 'unpaid'");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN split_price DECIMAL(10,2) DEFAULT 0.00");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets MODIFY COLUMN district VARCHAR(100) DEFAULT 'Tất cả'");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN checkin_code VARCHAR(30) DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN is_checked_in TINYINT(1) DEFAULT 0");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN checked_in_at DATETIME DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN noshow_status ENUM('none','pending','confirmed_noshow','rejected') DEFAULT 'none'");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN noshow_reason VARCHAR(255) DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN proposed_pitch_id INT DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE match_tickets ADD COLUMN proposed_by_user_id INT DEFAULT NULL");
            } catch (\Exception $e) {}

            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN host_checkin TINYINT(1) DEFAULT 0");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN guest_checkin TINYINT(1) DEFAULT 0");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN host_checkin_time DATETIME DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN guest_checkin_time DATETIME DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN noshow_reported_by INT DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN noshow_target_user_id INT DEFAULT NULL");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN noshow_status ENUM('none','pending','confirmed_noshow','rejected') DEFAULT 'none'");
            } catch (\Exception $e) {}
            try {
                $this->dbh->exec("ALTER TABLE matchmaking ADD COLUMN noshow_reason VARCHAR(255) DEFAULT NULL");
            } catch (\Exception $e) {}
        } catch (\Exception $e) {
            // Silently continue
        }
    }

    public function getConnection() {
        return $this->dbh;
    }
}
?>
