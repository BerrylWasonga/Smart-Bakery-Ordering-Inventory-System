<?php
/**
 * Core Functions & Helpers
 */

// ============================================================
// SESSION & SECURITY
// ============================================================

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_start();
    }
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

// ============================================================
// INPUT SANITIZATION
// ============================================================

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail(string $email): string|false {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeInt(mixed $value): int {
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

// ============================================================
// AUTHENTICATION
// ============================================================

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(APP_URL . '/pages/login.php');
    }
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $stmt = db()->prepare("SELECT id, name, email, phone, avatar, status FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function getCurrentAdmin(): ?array {
    if (!isAdminLoggedIn()) return null;
    $stmt = db()->prepare("SELECT id, name, email, role, avatar FROM admins WHERE id = ? AND status = 1");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

// ============================================================
// REDIRECT & URL
// ============================================================

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function createSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ============================================================
// FLASH MESSAGES
// ============================================================

function setFlash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

function getFlash(string $type): ?string {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

// ============================================================
// FORMATTING
// ============================================================

function formatPrice(float $amount): string {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

function formatDate(string $datetime): string {
    return date(DATE_FORMAT, strtotime($datetime));
}

function formatDateTime(string $datetime): string {
    return date(DATETIME_FORMAT, strtotime($datetime));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return formatDate($datetime);
}

function truncate(string $text, int $length = 150): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function generateOrderNumber(): string {
    return 'ORD-' . strtoupper(date('Ymd')) . '-' . strtoupper(substr(uniqid(), -6));
}

// ============================================================
// FILE UPLOAD
// ============================================================

function uploadImage(array $file, string $folder = 'products'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return $folder . '/' . $filename;
    }
    return false;
}

function getImageUrl(string $path, string $default = '/assets/images/no-image.jpg'): string {
    if (empty($path)) return APP_URL . $default;
    if (str_starts_with($path, 'http')) return $path;
    return UPLOAD_URL . $path;
}

// ============================================================
// FILE DELETE
// ============================================================

function deleteFile(string $path): bool
{
    if (empty($path)) return false;

    // prevent accidental path issues
    $fullPath = rtrim(UPLOAD_PATH, '/') . '/' . ltrim($path, '/');

    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}

// ============================================================
// SETTINGS
// ============================================================

function getSetting(string $key, string $default = ''): string {
    static $settings = [];
    if (empty($settings)) {
        try {
            $stmt = db()->query("SELECT `key`, `value` FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            return $default;
        }
    }
    return $settings[$key] ?? $default;
}

// ============================================================
// CART
// ============================================================

function getCartCount(): int {
    if (isLoggedIn()) {
        $stmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    return (int) $stmt->fetchColumn();
}

// ============================================================
// PAGINATION
// ============================================================

function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => ($currentPage - 1) * $perPage,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

function logActivity(string $userType, int $userId, string $action, string $desc = ''): void {
    try {
        $stmt = db()->prepare(
            "INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) VALUES (?,?,?,?,?,?)"
        );
        $stmt->execute([$userType, $userId, $action, $desc, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
    } catch (Exception $e) {}
}

function renderStars(float $rating): string {
    $html = '<div class="stars d-inline-flex gap-1">';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= round($rating) ? 'fas fa-star' : 'far fa-star';
        $html .= "<i class=\"{$class} text-warning\" style=\"font-size:0.85em\"></i>";
    }
    $html .= '</div>';
    return $html;
}
