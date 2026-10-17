<?php
/* ===== AUTOMIGZ — JSON API (signup, login, logout, bookings, cancel) ===== */
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(array $d, int $code = 200): void {
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}
function fail(string $msg, int $code = 400): void { out(['ok'=>false, 'error'=>$msg], $code); }
function text(array $in, string $key, int $max = 200): string {
    $v = $in[$key] ?? '';
    if (!is_string($v)) return '';
    $v = trim($v);
    return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}
function require_login(): array {
    $u = current_user();
    if (!$u) fail('Please log in first.', 401);
    return $u;
}

$action = $_GET['action'] ?? '';
$in = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        fail('Your session expired. Please refresh the page.', 403);
    }
    $decoded = json_decode(file_get_contents('php://input') ?: '', true);
    $in = is_array($decoded) ? $decoded : [];
}

try {
    switch ($action) {

    /* ---------- SIGN UP ---------- */
    case 'signup':
        $first  = text($in, 'first', 60);
        $last   = text($in, 'last', 60);
        $mobile = text($in, 'mobile', 20);
        $email  = strtolower(text($in, 'email', 120));
        $pass   = is_string($in['pass'] ?? null) ? $in['pass'] : '';

        if ($first === '' || $last === '' || $mobile === '' || $email === '' || $pass === '') fail('Please fill in all required fields.');
        if (!preg_match('/^[0-9+\s-]{7,20}$/', $mobile)) fail('Invalid mobile number: numbers only.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Please enter a valid email address.');
        if (strlen($pass) < 8) fail('Password must be at least 8 characters.');

        $st = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) fail('An account with this email already exists.');

        $st = db()->prepare('INSERT INTO users (first_name,last_name,mobile,email,pass_hash,created_at) VALUES (?,?,?,?,?,?)');
        $st->execute([$first, $last, $mobile, $email, password_hash($pass, PASSWORD_DEFAULT), gmdate('Y-m-d\TH:i:s\Z')]);
        out(['ok'=>true]);

    /* ---------- LOGIN (email or full name) ---------- */
    case 'login':
        $id   = strtolower(text($in, 'id', 120));
        $pass = is_string($in['pass'] ?? null) ? $in['pass'] : '';
        $st = db()->prepare("SELECT id, first_name AS first, last_name AS last, mobile, email, pass_hash FROM users
                             WHERE email = :id1 OR LOWER(CONCAT(first_name, ' ', last_name)) = :id2");
        $st->execute([':id1' => $id, ':id2' => $id]);
        $found = null;
        foreach ($st->fetchAll() as $u) {
            if (password_verify($pass, $u['pass_hash'])) { $found = $u; break; }
        }
        if (!$found) { usleep(300000); fail('Invalid email/username or password.', 401); }

        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$found['id'];
        if (!empty($in['remember'])) {   // stay logged in for 30 days
            setcookie(session_name(), session_id(), [
                'expires'  => time() + 30 * 86400,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),
            ]);
        }
        out(['ok'=>true, 'user'=>public_user($found)]);

    /* ---------- LOGOUT ---------- */
    case 'logout':
        unset($_SESSION['uid']);
        session_regenerate_id(true);
        out(['ok'=>true]);

    /* ---------- LIST MY BOOKINGS ---------- */
    case 'bookings':
        $u = require_login();
        $st = db()->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC');
        $st->execute([$u['id']]);
        out(['ok'=>true, 'bookings'=>array_map('format_booking', $st->fetchAll())]);

    /* ---------- CREATE BOOKING (price is calculated here, not trusted from the browser) ---------- */
    case 'book':
        $u = require_login();

        $car = null;
        foreach (cars() as $c) if ($c['id'] === (int)($in['carId'] ?? 0)) $car = $c;
        if (!$car) fail('Unknown car.');

        $color = null;
        foreach ($car['colors'] as $c) if ($c['n'] === ($in['color'] ?? '')) $color = $c;
        if (!$color) fail('Please choose a valid color.');

        $qty = (int)($in['qty'] ?? 1);
        if ($qty < 1 || $qty > 10) fail('Number of vehicles must be between 1 and 10.');

        $pick = text($in, 'pickDate', 10);
        $ret  = text($in, 'retDate', 10);
        $d1 = DateTime::createFromFormat('!Y-m-d', $pick);
        $d2 = DateTime::createFromFormat('!Y-m-d', $ret);
        if (!$d1 || !$d2 || $d1->format('Y-m-d') !== $pick || $d2->format('Y-m-d') !== $ret) fail('Please fill in all fields.');
        if ($d2 < $d1) fail('Return date must be after pick-up date.');
        if ($d1 < new DateTime('today')) fail('Pick-up date cannot be in the past.');
        $days = max(1, (int)round(($d2->getTimestamp() - $d1->getTimestamp()) / 86400));

        $mode = ($in['mode'] ?? '') === 'deliver' ? 'deliver' : 'pickup';
        $loc  = text($in, 'location', 120);
        if (!in_array($loc, LOCATIONS, true)) fail('Please choose a pick-up location.');
        $address = $mode === 'deliver' ? text($in, 'address', 200) : '';
        if ($mode === 'deliver' && $address === '') fail('Please enter your delivery address.');

        $renter = text($in, 'name', 100);
        $phone  = text($in, 'phone', 20);
        if ($renter === '' || !preg_match('/^[0-9+\s-]{7,20}$/', $phone)) fail('Please enter your name and a valid phone number.');

        $base  = $car['price'] * $qty * $days;
        $fee   = $mode === 'deliver' ? DELIVERY_FEE : 0;
        $total = $base + $fee;

        $st = db()->prepare('INSERT INTO bookings
            (user_id,car_id,car_name,img,price,color_name,color_hex,days,qty,base,fee,total,mode,location,address,renter,phone,pick_date,ret_date,status,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$u['id'], $car['id'], $car['name'], $car['img'], $car['price'], $color['n'], $color['h'],
                      $days, $qty, $base, $fee, $total, $mode, $loc, $address, $renter, $phone, $pick, $ret,
                      'active', gmdate('Y-m-d\TH:i:s\Z')]);
        $id = (int)db()->lastInsertId();
        $row = db()->query('SELECT * FROM bookings WHERE id = ' . $id)->fetch();
        out(['ok'=>true, 'booking'=>format_booking($row)]);

    /* ---------- CANCEL BOOKING (only your own, only if still active) ---------- */
    case 'cancel':
        $u = require_login();
        $st = db()->prepare("UPDATE bookings SET status='cancelled', cancelled_at=? WHERE id=? AND user_id=? AND status='active'");
        $st->execute([gmdate('Y-m-d\TH:i:s\Z'), (int)($in['id'] ?? 0), $u['id']]);
        if ($st->rowCount() === 0) fail('Booking not found or already cancelled.', 404);
        out(['ok'=>true]);

    default:
        fail('Unknown action.', 404);
    }
} catch (Throwable $e) {
    error_log('Automigz API error: ' . $e->getMessage());
    fail(DEBUG ? 'Server error: ' . $e->getMessage() : 'Server error. Please try again.', 500);
}
