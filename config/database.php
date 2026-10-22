<?php
// Database Configuration
// Support single DATABASE_URL or MYSQL_URL if provided by cloud platforms (Render, Railway, TiDB, etc.)
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
$parsedUrl = $dbUrl ? parse_url($dbUrl) : [];

$defaultHost = $parsedUrl['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1');
$defaultPort = $parsedUrl['port'] ?? (getenv('DB_PORT') ?: '3306');
$defaultUser = $parsedUrl['user'] ?? (getenv('DB_USER') ?: 'root');
$defaultPass = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$defaultName = !empty($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : (getenv('DB_NAME') ?: 'badminton_booking');

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

        // If connecting to remote cloud host (not localhost), disable strict SSL cert verify to ensure cloud DB connects seamlessly
        if ($this->host !== '127.0.0.1' && $this->host !== 'localhost' && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
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
            } else {
                die("
                <div style='max-width:650px; margin:50px auto; padding:30px; font-family:system-ui, sans-serif; background:#0b1116; color:#fff; border-radius:16px; border:1px solid #334155;'>
                    <h3 style='color:#f43f5e;'>Lỗi Kết Nối Cơ Sở Dữ Liệu</h3>
                    <p>Chi tiết: <code>" . htmlspecialchars($this->error) . "</code></p>
                </div>");
            }
        }
    }

    public function getConnection() {
        return $this->dbh;
    }
}
?>
