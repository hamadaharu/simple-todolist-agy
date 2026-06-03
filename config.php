<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'todo_app';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Silently fail if db is not set up yet
    $pdo_error = $e->getMessage();
}

// Custom Session Handler untuk mengatasi Serverless (Vercel)
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }
    
    #[\ReturnTypeWillChange]
    public function read($id) {
        if (!$this->pdo) return '';
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    }
    
    public function write($id, $data): bool {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, last_accessed) VALUES (?, ?, NOW())");
        return $stmt->execute([$id, $data]);
    }
    
    public function destroy($id): bool {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    #[\ReturnTypeWillChange]
    public function gc($max_lifetime) {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        return $stmt->execute([$max_lifetime]) ? $stmt->rowCount() : false;
    }
}

// Terapkan Session Handler sebelum session_start()
if (isset($pdo)) {
    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
?>
