<?php
/**
 * EPS (Easy Payment System) পেমেন্ট গেটওয়ে।
 *
 * ধাপ: GetToken → InitializeEPS (ইউজারকে EPS পেজে পাঠানো) → ফিরে এলে CheckMerchantTransactionStatus দিয়ে
 * সার্ভার থেকে যাচাই। ফেরার URL এর query string কখনো বিশ্বাস করা হয় না — শুধু Status API যা বলে।
 *
 * কনফিগ (config.php / config.local.php):
 *   'eps' => [
 *       'enabled'     => true,
 *       'mode'        => 'sandbox',   // 'sandbox' বা 'live'
 *       'merchant_id' => '...', 'store_id' => '...', 'username' => '...', 'password' => '...', 'hash_key' => '...',
 *       'return_base' => '',          // ঐচ্ছিক, যেমন 'https://mashtermoshai.com' — না দিলে রিকোয়েস্ট থেকে নেয়
 *       'only_users'  => [],          // ঐচ্ছিক: শুধু এই ইউজার Id গুলো EPS দেখবে (খালি = সবাই)
 *   ],
 * কনফিগ না থাকলে / enabled=false হলে চেকআউটে EPS দেখায় না — সাইট আগের মতোই চলে।
 */
class Eps
{
    private const BASE = ['sandbox' => 'https://sandboxpgapi.eps.com.bd/v1', 'live' => 'https://pgapi.eps.com.bd/v1'];
    public const STATUSES = ['Initiated', 'Finalizing', 'Paid', 'Failed', 'Cancelled', 'Mismatch'];
    private static bool $tableReady = false;

    public static function cfg(string $k, $default = null) { return config('eps.' . $k, $default); }

    /** EPS চালু আর সব key দেওয়া আছে কিনা */
    public static function enabled(): bool
    {
        if (!self::cfg('enabled', false)) return false;
        foreach (['merchant_id', 'store_id', 'username', 'password', 'hash_key'] as $k) if (trim((string)self::cfg($k, '')) === '') return false;
        return function_exists('curl_init');
    }

    /**
     * এই ইউজার EPS বাটন দেখবে / পেমেন্ট শুরু করতে পারবে কিনা।
     * config এ 'only_users' => [ইউজার Id, ...] দিলে শুধু তারা — লাইভে প্রথমে নিজে একটা আসল পেমেন্ট করে দেখার জন্য।
     * খালি রাখলে সবাই। (ফেরা / যাচাই সবসময় চলে — যাতে শুরু হওয়া কোনো পেমেন্ট হারিয়ে না যায়।)
     */
    public static function availableFor(?int $userId): bool
    {
        if (!self::enabled()) return false;
        $only = array_filter(array_map('intval', (array)self::cfg('only_users', [])));
        return !$only || ($userId !== null && in_array($userId, $only, true));
    }

    public static function isSandbox(): bool { return self::cfg('mode', 'sandbox') !== 'live'; }

    private static function base(): string { return self::BASE[self::isSandbox() ? 'sandbox' : 'live']; }

    /** x-hash = Base64( HMAC-SHA512( key = UTF-8 hash key, data = value ) ) */
    public static function hash(string $value): string
    {
        return base64_encode(hash_hmac('sha512', $value, (string)self::cfg('hash_key', ''), true));
    }

    /** @return array [httpCode, decoded body array|null, raw body] */
    private static function request(string $method, string $path, string $hashOf, ?array $body = null, ?string $token = null): array
    {
        $ch = curl_init(self::base() . $path);
        $headers = ['Accept: application/json', 'x-hash: ' . self::hash($hashOf)];
        if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 25, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2];
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false) throw new RuntimeException('EPS connection failed: ' . $err);
        $data = json_decode((string)$raw, true);
        return [$code, is_array($data) ? self::lowerKeys($data) : null, (string)$raw];
    }

    /** EPS কখনো "TransactionId", কখনো "transactionId" পাঠায় — সব key ছোট হাতের করে নেওয়া */
    private static function lowerKeys(array $a): array
    {
        $o = [];
        foreach ($a as $k => $v) $o[is_string($k) ? strtolower($k) : $k] = is_array($v) ? self::lowerKeys($v) : $v;
        return $o;
    }

    public static function getToken(): string
    {
        $user = (string)self::cfg('username');
        [$code, $d, $raw] = self::request('POST', '/Auth/GetToken', $user, ['userName' => $user, 'password' => (string)self::cfg('password')]);
        $tok = (string)($d['token'] ?? '');
        if ($tok === '') throw new RuntimeException("EPS GetToken failed (HTTP $code): " . mb_substr($raw, 0, 300));
        return $tok;
    }

    /**
     * পেমেন্ট শুরু — সফল হলে ['redirect' => EPS পেজের URL, 'epsTxnId' => ...]
     * $o: merchantTxnId, orderId, amount, successUrl, failUrl, cancelUrl, name, email, phone, address, city, productName, productCategory
     */
    public static function initialize(array $o): array
    {
        $token = self::getToken();
        $addr = trim((string)($o['address'] ?? '')) ?: 'Bangladesh';
        $city = trim((string)($o['city'] ?? '')) ?: 'Dhaka';
        $body = [
            'merchantId' => (string)self::cfg('merchant_id'),
            'storeId' => (string)self::cfg('store_id'),
            'CustomerOrderId' => (string)$o['orderId'],
            'merchantTransactionId' => (string)$o['merchantTxnId'],
            'transactionTypeId' => 1,          // 1 = Web
            'financialEntityId' => 0,
            'transitionStatusId' => 0,
            'totalAmount' => (float)$o['amount'],
            'ipAddress' => (string)($o['ip'] ?? ''),
            'version' => '1',
            'successUrl' => (string)$o['successUrl'],
            'failUrl' => (string)$o['failUrl'],
            'cancelUrl' => (string)$o['cancelUrl'],
            'customerName' => mb_substr(trim((string)($o['name'] ?? '')) ?: 'Customer', 0, 100),
            'customerEmail' => (string)($o['email'] ?? '') ?: 'noreply@mashtermoshai.com',
            'CustomerAddress' => mb_substr($addr, 0, 200),
            'CustomerAddress2' => '',
            'CustomerCity' => mb_substr($city, 0, 60),
            'CustomerState' => mb_substr($city, 0, 60),
            'CustomerPostcode' => (string)($o['postcode'] ?? '') ?: '1000',
            'CustomerCountry' => 'BD',
            'CustomerPhone' => (string)($o['phone'] ?? '') ?: '01000000000',
            'ShipmentName' => '', 'ShipmentAddress' => '', 'ShipmentAddress2' => '', 'ShipmentCity' => '', 'ShipmentState' => '', 'ShipmentPostcode' => '', 'ShipmentCountry' => '',
            'ValueA' => (string)($o['valueA'] ?? ''), 'ValueB' => '', 'ValueC' => '', 'ValueD' => '',
            'ShippingMethod' => 'NO',
            'NoOfItem' => '1',
            'ProductName' => mb_substr((string)$o['productName'], 0, 100),
            'ProductProfile' => 'general',
            'ProductCategory' => mb_substr((string)($o['productCategory'] ?? 'Education'), 0, 60),
        ];
        [$code, $d, $raw] = self::request('POST', '/EPSEngine/InitializeEPS', (string)$o['merchantTxnId'], $body, $token);
        $url = (string)($d['redirecturl'] ?? '');
        if ($url === '' || !preg_match('#^https://[a-z0-9.-]*eps\.com\.bd/#i', $url)) {
            throw new RuntimeException("EPS InitializeEPS failed (HTTP $code): " . mb_substr($raw, 0, 400));
        }
        return ['redirect' => $url, 'epsTxnId' => (string)($d['transactionid'] ?? '')];
    }

    /** সার্ভার থেকে লেনদেনের অবস্থা যাচাই — EPS এর উত্তর (ছোট হাতের key), না পেলে exception */
    public static function verify(string $merchantTxnId): array
    {
        $token = self::getToken();
        [$code, $d, $raw] = self::request('GET', '/EPSEngine/CheckMerchantTransactionStatus?merchantTransactionId=' . rawurlencode($merchantTxnId), $merchantTxnId, null, $token);
        if (!$d) throw new RuntimeException("EPS status check failed (HTTP $code): " . mb_substr($raw, 0, 300));
        $d['_raw'] = mb_substr($raw, 0, 4000);
        return $d;
    }

    /** ইউনিক, অন্তত ১০ অংকের merchantTransactionId (EPS এর নিয়ম) */
    public static function newMerchantTxnId(): string
    {
        return date('ymdHis') . str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ============================================================
    // ===== EpsTransactions টেবিল: প্রতিটা EPS চেষ্টার রেকর্ড =====
    // ============================================================
    public static function ensureTable(): void
    {
        if (self::$tableReady) return;
        $pdo = pdo();
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS "EpsTransactions" ("Id" INTEGER PRIMARY KEY AUTOINCREMENT, "MerchantTxnId" TEXT NOT NULL UNIQUE, "UserId" INTEGER NOT NULL,
                "Amount" INTEGER NOT NULL, "Mode" TEXT NOT NULL, "OrderJson" TEXT NOT NULL, "Status" TEXT NOT NULL DEFAULT \'Initiated\', "EpsTxnId" TEXT NULL,
                "FinancialEntity" TEXT NULL, "PaymentRequestId" INTEGER NULL, "LastResponse" TEXT NULL, "CreatedAt" TEXT NOT NULL, "UpdatedAt" TEXT NOT NULL)');
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `EpsTransactions` (`Id` INT NOT NULL AUTO_INCREMENT, `MerchantTxnId` VARCHAR(30) NOT NULL, `UserId` INT NOT NULL,
                `Amount` INT NOT NULL, `Mode` VARCHAR(8) NOT NULL, `OrderJson` TEXT NOT NULL, `Status` VARCHAR(12) NOT NULL DEFAULT 'Initiated', `EpsTxnId` VARCHAR(60) NULL,
                `FinancialEntity` VARCHAR(60) NULL, `PaymentRequestId` INT NULL, `LastResponse` TEXT NULL, `CreatedAt` DATETIME NOT NULL, `UpdatedAt` DATETIME NOT NULL,
                PRIMARY KEY (`Id`), UNIQUE KEY `UX_EPS_Mtx` (`MerchantTxnId`), KEY `IX_EPS_User` (`UserId`, `Status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        self::$tableReady = true;
    }

    public static function create(int $userId, int $amount, array $order): Obj
    {
        self::ensureTable();
        $mtx = self::newMerchantTxnId();
        $id = db()->insert('INSERT INTO `EpsTransactions` (`MerchantTxnId`, `UserId`, `Amount`, `Mode`, `OrderJson`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES (:m, :u, :a, :md, :o, \'Initiated\', :t, :t2)',
            ['m' => $mtx, 'u' => $userId, 'a' => $amount, 'md' => self::isSandbox() ? 'sandbox' : 'live', 'o' => json_encode($order, JSON_UNESCAPED_UNICODE), 't' => now(), 't2' => now()]);
        return self::get($id);
    }

    public static function get(int $id): ?Obj
    {
        self::ensureTable();
        $r = db()->one('SELECT * FROM `EpsTransactions` WHERE `Id` = :i', ['i' => $id]);
        if ($r) { $r->Id = (int)$r->Id; $r->UserId = (int)$r->UserId; $r->Amount = (int)$r->Amount; $r->OrderData = json_decode((string)$r->OrderJson, true) ?: []; }
        return $r;
    }

    public static function update(int $id, array $set, ?string $onlyIfStatus = null): int
    {
        self::ensureTable();
        $set['UpdatedAt'] = now();
        $sql = 'UPDATE `EpsTransactions` SET ' . implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($set))) . ' WHERE `Id` = :__id';
        $p = $set + ['__id' => $id];
        if ($onlyIfStatus !== null) { $sql .= ' AND `Status` = :__st'; $p['__st'] = $onlyIfStatus; }
        return db()->exec($sql, $p);
    }

    /**
     * ইউজারের শেষ না হওয়া EPS লেনদেন — "আমার পেমেন্ট" পেজে আবার যাচাই হয়, যাতে টাকা কেটে যাওয়ার পর
     * ব্রাউজার বন্ধ হয়ে গেলেও পেমেন্টটা হারিয়ে না যায়। (Initiated: ২৪ ঘণ্টা; Cancelled/Failed: শেষ ১ ঘণ্টা)
     */
    public static function openForUser(int $userId, int $limit = 2): array
    {
        self::ensureTable();
        $rows = db()->all("SELECT `Id` FROM `EpsTransactions` WHERE `UserId` = :u AND ((`Status` = 'Initiated' AND `CreatedAt` >= :d)
            OR (`Status` IN ('Cancelled', 'Failed') AND `UpdatedAt` >= :h)) ORDER BY `Id` DESC LIMIT " . max(1, $limit),
            ['u' => $userId, 'd' => date('Y-m-d H:i:s', time() - 86400), 'h' => date('Y-m-d H:i:s', time() - 3600)]);
        return array_map(fn($r) => self::get((int)$r->Id), $rows);
    }
}
