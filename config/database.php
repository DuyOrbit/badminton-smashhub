<?php
// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); 
define('DB_NAME', getenv('DB_NAME') ?: 'badminton_booking');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $error;

    public function __construct() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            $this->dbh->exec("set names utf8mb4");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Important for demo: if db doesn't exist, warn the user.
            if (strpos($this->error, 'Unknown database') !== false) {
                 die("<div style='padding:20px; font-family:sans-serif;'><h2>Lỗi Hệ Thống</h2><p>Không tìm thấy database <strong>'{$this->dbname}'</strong>.</p><p>Vui lòng tạo database bằng cách import file <strong>badminton_booking.sql</strong> vào MySQL (thông qua phpMyAdmin).</p></div>");
            } else {
                 die("Lỗi kết nối CSDL: " . $this->error);
            }
        }
    }

    public function getConnection() {
        return $this->dbh;
    }
}
?>
