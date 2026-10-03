<?php
declare(strict_types=1);

/* ---------- Настройки сайта (всё редактируется в админке) ---------- */

function settings_schema(): array
{
    // key => [группа, подпись, тип, значение по умолчанию, подсказка]
    return [
        'site_name'        => ['Компания', 'Название сайта', 'text', 'Leon_pro', ''],
        'slogan'           => ['Компания', 'Слоган', 'text', 'Каждый проект с нами безупречен', ''],
        'company_short'    => ['Компания', 'Описание в подвале', 'textarea', 'Производитель тарельчатых дюбелей, площадок под стяжку, якорных скоб и СВП.', ''],
        'logo_image'       => ['Компания', 'Логотип (картинка)', 'image', '', 'Если не загружен — используется встроенный знак'],
        'accent_color'     => ['Компания', 'Фирменный цвет', 'color', '#F07F1F', 'Цвет кнопок и акцентов'],

        'phone'            => ['Контакты', 'Телефон', 'text', '[телефон]', ''],
        'email'            => ['Контакты', 'Email', 'text', '[email]', ''],
        'address'          => ['Контакты', 'Адрес производства и склада', 'text', 'г. Краснодар, [адрес]', ''],
        'work_hours'       => ['Контакты', 'Часы работы', 'text', '[часы работы]', ''],
        'telegram'         => ['Контакты', 'Ссылка на Telegram', 'text', '', 'Например, https://t.me/username'],
        'whatsapp'         => ['Контакты', 'Ссылка на WhatsApp', 'text', '', 'Например, https://wa.me/79000000000'],
        'map_embed'        => ['Контакты', 'Код карты (Яндекс.Карты)', 'code', '', 'Вставьте iframe из конструктора карт'],

        'topbar_text'      => ['Главная', 'Текст верхней полосы', 'text', 'Производство и склад — Краснодар · Отгрузка транспортными компаниями', ''],
        'hero_eyebrow'     => ['Главная', 'Надзаголовок', 'text', 'Производитель · Краснодар', ''],
        'hero_title'       => ['Главная', 'Заголовок первого экрана', 'text', 'Крепёж для теплоизоляции и стяжки — оптом от производителя', ''],
        'hero_text'        => ['Главная', 'Текст первого экрана', 'textarea', 'Тарельчатые дюбели, площадки под стяжку, якорные скобы и СВП со склада в Краснодаре. Оптовые цены для строительных компаний, подрядчиков и бригад.', ''],
        'hero_image'       => ['Главная', 'Картинка первого экрана', 'image', '', 'Если не загружена — показывается логотип на тёмном фоне'],

        'min_order_qty'    => ['Заказы', 'Минимальный заказ, шт.', 'int', '5000', 'Суммарно по корзине'],
        'default_step'     => ['Заказы', 'Шаг количества по умолчанию, шт.', 'int', '500', 'Если у товара не указана кратность'],
        'show_prices'      => ['Заказы', 'Показывать цены', 'bool', '1', 'Если выключено — «Цена по запросу»'],
        'price_note'       => ['Заказы', 'Пометка к ценам', 'text', 'Оптовая цена за штуку', 'Например, «Цены с НДС» после перехода на НДС'],
        'delivery_note'    => ['Заказы', 'Коротко о доставке', 'textarea', 'Отгрузка со склада в Краснодаре через транспортные компании. Доставку оплачивает покупатель.', ''],
        'order_success'    => ['Заказы', 'Текст после отправки заявки', 'textarea', 'Менеджер свяжется с вами, подтвердит состав заказа, согласует стоимость и транспортную компанию.', ''],

        'manager_email'    => ['Уведомления', 'Email менеджера для новых заказов', 'text', '', 'Можно несколько через запятую'],
        'mail_from'        => ['Уведомления', 'Адрес отправителя писем', 'text', '', 'Например, noreply@ваш-домен.ru (ящик на вашем домене)'],

        'seo_title'        => ['SEO', 'Заголовок сайта (title)', 'text', 'Leon_pro — тарельчатые дюбели, площадки под стяжку, якорные скобы и СВП оптом', ''],
        'seo_description'  => ['SEO', 'Описание (description)', 'textarea', 'Производитель крепежа для теплоизоляции и стяжки. Оптовые поставки со склада в Краснодаре.', ''],
        'head_code'        => ['SEO', 'Код счётчиков (Яндекс.Метрика и т. п.)', 'code', '', 'Вставляется в <head> всех страниц'],
    ];
}

function setting(string $key): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (q('SELECT key, value FROM settings')->fetchAll() as $r) {
            $cache[$r['key']] = (string)$r['value'];
        }
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $schema = settings_schema();
    return isset($schema[$key]) ? (string)$schema[$key][3] : '';
}

function setting_save(string $key, string $value): void
{
    q('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value', [$key, $value]);
}

function min_order(): int
{
    return max(0, (int)setting('min_order_qty'));
}

/* ---------- Общие функции ---------- */

function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 0;
    return url($path) . '?v=' . $v;
}

function upload_url(?string $rel): string
{
    return $rel ? url('uploads/' . $rel) : '';
}

function redirect(string $path): never
{
    $to = preg_match('~^https?://~', $path) ? $path : url($path);
    header('Location: ' . $to);
    exit;
}

function back(string $fallback = ''): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = preg_replace('~:\d+$~', '', $_SERVER['HTTP_HOST'] ?? '');
    if ($ref && parse_url($ref, PHP_URL_HOST) === $host) {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback);
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Сессия устарела. Обновите страницу и попробуйте ещё раз.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $k, string $default = ''): string
{
    return trim((string)($_POST[$k] ?? $default));
}

function fmt_qty($n): string
{
    return number_format((float)$n, 0, ',', ' ');
}

function fmt_money($n): string
{
    $n = (float)$n;
    $dec = abs($n - round($n)) > 0.0001 ? 2 : 0;
    return number_format($n, $dec, ',', ' ') . ' ₽';
}

function price_label(array $p): string
{
    if (setting('show_prices') !== '1' || (float)$p['price'] <= 0) {
        return 'По запросу';
    }
    return fmt_money($p['price']);
}

function product_step(array $p): int
{
    $s = (int)($p['step'] ?? 0);
    return $s > 0 ? $s : max(1, (int)setting('default_step'));
}

function order_statuses(): array
{
    return [
        'new'       => 'Заявка принята',
        'confirmed' => 'Согласован',
        'payment'   => 'Ожидает оплаты',
        'shipped'   => 'Передан в ТК',
        'done'      => 'Получен',
        'cancelled' => 'Отменён',
    ];
}

function status_label(string $s): string
{
    return order_statuses()[$s] ?? $s;
}

function normalize_phone(string $p): string
{
    $d = preg_replace('~\D+~', '', $p);
    if (strlen($d) === 11 && ($d[0] === '8' || $d[0] === '7')) {
        $d = '7' . substr($d, 1);
    } elseif (strlen($d) === 10) {
        $d = '7' . $d;
    }
    return $d;
}

/* ---------- Загрузка файлов ---------- */

function handle_upload(string $field, array $allowedExt, string $subdir): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Файл не загрузился (код ' . $f['error'] . '). Проверьте размер файла.');
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Недопустимый тип файла. Разрешено: ' . implode(', ', $allowedExt));
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $okMime = [
        'jpg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
        'svg' => ['image/svg+xml', 'text/xml', 'text/plain', 'application/xml'], 'pdf' => ['application/pdf'],
    ];
    if (isset($okMime[$ext]) && !in_array($mime, $okMime[$ext], true)) {
        throw new RuntimeException('Содержимое файла не соответствует расширению.');
    }
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $base = preg_replace('~[^a-z0-9_-]+~i', '-', pathinfo($f['name'], PATHINFO_FILENAME));
    $base = trim(substr((string)$base, 0, 40), '-') ?: 'file';
    $name = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Не удалось сохранить файл. Проверьте права на папку uploads.');
    }
    return $subdir . '/' . $name;
}

function delete_upload(?string $rel): void
{
    if (!$rel) {
        return;
    }
    $path = realpath(UPLOAD_DIR . '/' . $rel);
    $root = realpath(UPLOAD_DIR);
    if ($path && $root && str_starts_with($path, $root . DIRECTORY_SEPARATOR) && is_file($path)) {
        @unlink($path);
    }
}

/* ---------- Корзина ---------- */

function cart_raw(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_set(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function cart_items(): array
{
    $raw = cart_raw();
    if (!$raw) {
        return [];
    }
    $ids = array_map('intval', array_keys($raw));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $rows = q("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id IN ($in) AND p.active = 1", $ids)->fetchAll();
    $items = [];
    foreach ($rows as $r) {
        $r['qty'] = (int)$raw[$r['id']];
        $r['line_sum'] = (float)$r['price'] * $r['qty'];
        $items[] = $r;
    }
    return $items;
}

function cart_count(): int
{
    return count(cart_raw());
}

function cart_total_qty(): int
{
    return array_sum(cart_raw());
}

/* ---------- Пользователи ---------- */

function current_user(): ?array
{
    static $u = false;
    if ($u !== false) {
        return $u;
    }
    $u = null;
    if (!empty($_SESSION['user_id'])) {
        $u = q('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']])->fetch() ?: null;
    }
    return $u;
}

function require_user(): array
{
    $u = current_user();
    if (!$u) {
        flash('Войдите или зарегистрируйтесь, чтобы открыть личный кабинет.', 'info');
        redirect('login.php');
    }
    return $u;
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return q('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']])->fetch() ?: null;
}

function require_admin(): array
{
    $a = current_admin();
    if (!$a) {
        redirect('admin/login.php');
    }
    return $a;
}

/* ---------- Почта ---------- */

function send_mail(string $to, string $subject, string $body): bool
{
    $to = trim($to);
    if ($to === '') {
        return false;
    }
    $host = preg_replace('~:\d+$~', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $from = setting('mail_from') ?: ('noreply@' . $host);
    $headers = [
        'From: =?UTF-8?B?' . base64_encode(setting('site_name')) . '?= <' . $from . '>',
        'Reply-To: ' . (filter_var(setting('email'), FILTER_VALIDATE_EMAIL) ? setting('email') : $from),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers), '-f' . $from);
    @file_put_contents(preg_replace('~\.sqlite$~', '-mail.log', db_file()),date('c') . ' | ' . ($ok ? 'OK ' : 'FAIL') . " | $to | $subject\n", FILE_APPEND);
    return $ok;
}
