<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_path(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    foreach (['/student/', '/admin/', '/actions/'] as $marker) {
        $position = strpos($scriptName, $marker);
        if ($position !== false) {
            return rtrim(substr($scriptName, 0, $position), '/');
        }
    }

    return rtrim(dirname($scriptName), '/.');
}

function url(string $path = ''): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('The form expired. Go back, refresh the page, and try again.');
    }
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function full_name(array $user): string
{
    return trim(implode(' ', array_filter([
        $user['F_name'] ?? '',
        $user['m_name'] ?? '',
        $user['L_name'] ?? '',
    ])));
}

function initials(array $user): string
{
    $first = mb_substr((string) ($user['F_name'] ?? 'S'), 0, 1);
    $last = mb_substr((string) ($user['L_name'] ?? ''), 0, 1);
    return mb_strtoupper($first . $last);
}

function refresh_session_user(mysqli $conn): void
{
    if (!current_user_id()) {
        return;
    }
    $stmt = $conn->prepare('SELECT * FROM `USER` WHERE user_id = ? LIMIT 1');
    $userId = current_user_id();
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['role'];
    }
}

function status_class(?string $status): string
{
    return match (strtolower((string) $status)) {
        'active', 'accepted', 'completed', 'paid', 'available' => 'success',
        'pending', 'unpaid' => 'warning',
        'inactive', 'cancelled', 'rejected', 'suspended' => 'danger',
        default => 'secondary',
    };
}

function allowed_session_statuses(): array
{
    return ['pending', 'accepted', 'rejected', 'completed', 'cancelled'];
}

function allowed_payment_statuses(): array
{
    return ['unpaid', 'paid', 'waived'];
}

function selected(string $value, ?string $current): string
{
    return $value === $current ? ' selected' : '';
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return 'Not scheduled';
    }
    return date('M j, Y - g:i A', strtotime($value));
}

function query_value(mysqli $conn, string $sql, array $params = [], string $types = ''): mixed
{
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    return $row[0] ?? null;
}
