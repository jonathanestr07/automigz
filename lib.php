<?php
/* ===== AUTOMIGZ — shared helpers (session, database, car data) ===== */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

date_default_timezone_set('Asia/Manila');

/* Set to false before going online: shows the real error text on the page while true. */
const DEBUG = true;

const DELIVERY_FEE = 500;
const LOCATIONS = [
    "Automigz Office — Cabanatuan City",
    "Automigz Branch — Manila",
    "Automigz Branch — Clark, Pampanga",
    "Automigz Branch — Tarlac City",
    "Nueva Ecija Convention Center",
    "Cabanatuan Airport Road Hub",
];

/* Single source of truth for cars: the server uses these prices, so customers can't change them. */
function cars(): array {
    return [
        ['id'=>1,'name'=>'Toyota Fortuner','type'=>'SUV','price'=>5000,'trans'=>'Automatic Transmission','img'=>'images/img1.png',
         'info'=>'7-seater 4x4 SUV · 2.4L Diesel · 201 HP · Perfect for family trips',
         'colors'=>[['n'=>'Super White','h'=>'#f4f4f4'],['n'=>'Silver Metallic','h'=>'#c0c0c8'],['n'=>'Attitude Black','h'=>'#1c1c1e'],['n'=>'Red Mica','h'=>'#a11212']]],
        ['id'=>2,'name'=>'Mitsubishi Montero','type'=>'SUV','price'=>4500,'trans'=>'Automatic Transmission','img'=>'images/landcruiser.png',
         'info'=>'7-seater SUV · 2.4L Diesel · 178 HP · Sporty and fuel efficient',
         'colors'=>[['n'=>'White Diamond','h'=>'#f6f6f6'],['n'=>'Titanium Gray','h'=>'#8a8f94'],['n'=>'Jet Black','h'=>'#17181a'],['n'=>'Deep Bronze','h'=>'#6e5a3a']]],
        ['id'=>3,'name'=>'Isuzu Dmax','type'=>'SUV','price'=>4000,'trans'=>'Automatic Transmission','img'=>'images/vehicle-placeholder.svg',
         'info'=>'Rugged 4x4 · 1.9L Diesel · 148 HP · Tough and dependable',
         'colors'=>[['n'=>'Silky Pearl White','h'=>'#f2f2f2'],['n'=>'Graphite Gray','h'=>'#7d8287'],['n'=>'Onyx Black','h'=>'#141517'],['n'=>'Red Spinel','h'=>'#8e1b25']]],
        ['id'=>4,'name'=>'Toyota Land Cruiser','type'=>'SUV','price'=>30000,'trans'=>'Automatic Transmission','img'=>'images/image 33.png',
         'info'=>'Full-size luxury 4x4 · 3.3L Twin-Turbo Diesel · 304 HP',
         'colors'=>[['n'=>'Precious White','h'=>'#f2f2f0'],['n'=>'Silver','h'=>'#bfc3c9'],['n'=>'Attitude Black','h'=>'#151517'],['n'=>'Dark Blue','h'=>'#1d3a5f']]],
        ['id'=>5,'name'=>'Ford Bronco','type'=>'SUV','price'=>9000,'trans'=>'Automatic Transmission','img'=>'images/image 34.png',
         'info'=>'Off-road icon · 2.7L EcoBoost V6 · 330 HP · Built for adventure',
         'colors'=>[['n'=>'Race Red','h'=>'#b4202a'],['n'=>'Area 51 Blue','h'=>'#5e7f83'],['n'=>'Shadow Black','h'=>'#131416'],['n'=>'Cactus Gray','h'=>'#a9b0a4']]],
        ['id'=>6,'name'=>'Nissan Patrol','type'=>'SUV','price'=>15000,'trans'=>'Automatic Transmission','img'=>'images/image 32.png',
         'info'=>'Full-size SUV · 5.6L V8 · 400 HP · Premium comfort and power',
         'colors'=>[['n'=>'Pearl White','h'=>'#f5f5f3'],['n'=>'Silver','h'=>'#bcc0c6'],['n'=>'Black Obsidian','h'=>'#121214'],['n'=>'Dark Red','h'=>'#6e1020']]],
        ['id'=>7,'name'=>'Toyota Hiace','type'=>'Van','price'=>5000,'trans'=>'Automatic Transmission','img'=>'images/vehicle-placeholder.svg',
         'info'=>'15-seater commuter van · 2.8L Diesel · Ideal for group travel',
         'colors'=>[['n'=>'Super White','h'=>'#f4f4f4'],['n'=>'Silver Mica','h'=>'#c4c6cc'],['n'=>'Black','h'=>'#1a1a1c']]],
        ['id'=>8,'name'=>'Toyota Hilux','type'=>'Pick-up','price'=>8000,'trans'=>'Automatic Transmission','img'=>'images/vehicle-placeholder.svg',
         'info'=>'4x4 pick-up · 2.8L Diesel · 201 HP · Built for heavy duty',
         'colors'=>[['n'=>'Super White','h'=>'#f4f4f4'],['n'=>'Silver Metallic','h'=>'#c0c0c8'],['n'=>'Black','h'=>'#191a1c'],['n'=>'Orange Metallic','h'=>'#d9622b']]],
    ];
}

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.gc_maxlifetime', (string)(30 * 86400));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* MySQL database (phpMyAdmin) — database and tables are created automatically on first use */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException('PHP extension pdo_mysql is not enabled. In php.ini remove the ; before extension=pdo_mysql, then restart Apache.');
    }
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $base = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';

    try {
        try {
            $pdo = new PDO($base . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            $code = (int)($e->errorInfo[1] ?? $e->getCode());
            if ($code !== 1049) throw $e;             // 1049 = database doesn't exist yet
            $tmp = new PDO($base, DB_USER, DB_PASS, $opts);
            $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = new PDO($base . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $opts);
        }
    } catch (PDOException $e) {
        $code = (int)($e->errorInfo[1] ?? $e->getCode());
        if ($code === 2002 || $code === 2006) {
            throw new RuntimeException('Cannot connect to MySQL. Open the XAMPP Control Panel and click Start next to MySQL.');
        }
        if ($code === 1045) {
            throw new RuntimeException('MySQL refused the username/password. Check DB_USER and DB_PASS in config.php.');
        }
        throw $e;
    }

    // create tables the first time
    if (!$pdo->query("SHOW TABLES LIKE 'bookings'")->fetch()) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(60) NOT NULL,
            last_name VARCHAR(60) NOT NULL,
            mobile VARCHAR(20) NOT NULL,
            email VARCHAR(120) NOT NULL UNIQUE,
            pass_hash VARCHAR(255) NOT NULL,
            created_at VARCHAR(24) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            car_id INT NOT NULL,
            car_name VARCHAR(100) NOT NULL,
            img VARCHAR(255) NOT NULL,
            price INT NOT NULL,
            color_name VARCHAR(60) NOT NULL,
            color_hex VARCHAR(10) NOT NULL,
            days INT NOT NULL,
            qty INT NOT NULL,
            base INT NOT NULL,
            fee INT NOT NULL,
            total INT NOT NULL,
            mode VARCHAR(10) NOT NULL,
            location VARCHAR(120) NOT NULL,
            address VARCHAR(200) NOT NULL DEFAULT '',
            renter VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            pick_date VARCHAR(10) NOT NULL,
            ret_date VARCHAR(10) NOT NULL,
            status VARCHAR(12) NOT NULL DEFAULT 'active',
            created_at VARCHAR(24) NOT NULL,
            cancelled_at VARCHAR(24) NULL,
            INDEX idx_user (user_id),
            CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    return $pdo;
}

function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $st = db()->prepare('SELECT id, first_name AS first, last_name AS last, mobile, email FROM users WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    $u = $st->fetch();
    return $u ?: null;
}

/* The shape app.js expects */
function public_user(?array $u): ?array {
    return $u ? ['first'=>$u['first'], 'last'=>$u['last'], 'mobile'=>$u['mobile'], 'email'=>$u['email']] : null;
}

function format_booking(array $r): array {
    return [
        'id'       => (int)$r['id'],
        'car'      => $r['car_name'],
        'img'      => $r['img'],
        'price'    => (int)$r['price'],
        'color'    => ['n'=>$r['color_name'], 'h'=>$r['color_hex']],
        'days'     => (int)$r['days'],
        'qty'      => (int)$r['qty'],
        'base'     => (int)$r['base'],
        'fee'      => (int)$r['fee'],
        'total'    => (int)$r['total'],
        'mode'     => $r['mode'],
        'location' => $r['location'],
        'address'  => $r['address'],
        'renter'   => $r['renter'],
        'phone'    => $r['phone'],
        'pickDate' => $r['pick_date'],
        'retDate'  => $r['ret_date'],
        'status'   => $r['status'],
        'date'     => $r['created_at'],
    ];
}
