<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_layout.php';

require_admin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

function fail_save(string $message): never
{
    http_response_code(422);

    exit(
        'Unable to save event: ' .
        h($message) .
        ' <a href="javascript:history.back()">Go back</a>'
    );
}

$id = (int)($_POST['id'] ?? 0);

$name = trim((string)($_POST['event_name'] ?? ''));
$location = trim((string)($_POST['location'] ?? ''));
$date = (string)($_POST['event_date'] ?? '');
$time = (string)($_POST['event_time'] ?? '');

$description = trim(
    (string)($_POST['description'] ?? '')
);

$status = (string)($_POST['status'] ?? 'draft');

if (
    $name === '' ||
    mb_strlen($name) > 200 ||
    $location === '' ||
    mb_strlen($location) > 255 ||
    mb_strlen($description) > 2000
) {
    fail_save('Check the event name, location and description.');
}

$d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

if (
    !$d ||
    $d->format('Y-m-d') !== $date ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)
) {
    fail_save('Invalid event date or time.');
}

if (!in_array($status, ['draft', 'published'], true)) {
    fail_save('Invalid publication status.');
}

// Ticket package fields
$names = $_POST['package_name'] ?? [];
$descs = $_POST['package_description'] ?? [];
$prices = $_POST['package_price'] ?? [];
$benefits = $_POST['package_benefits'] ?? [];
$limits = $_POST['package_limit'] ?? [];
$active = $_POST['package_active'] ?? [];

if (
    !is_array($names) ||
    count($names) < 1 ||
    count($names) > 30
) {
    fail_save('Add between 1 and 30 ticket packages.');
}

$validated = [];

foreach ($names as $key => $packageName) {
    $packageName = trim((string)$packageName);

    $packageDescription = trim(
        (string)($descs[$key] ?? '')
    );

    $price = (string)($prices[$key] ?? '');
    $limit = (string)($limits[$key] ?? '');

    if (
        $packageName === '' ||
        mb_strlen($packageName) > 100 ||
        mb_strlen($packageDescription) > 2000 ||
        !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price)
    ) {
        fail_save(
            'Invalid package name, description or price.'
        );
    }

    if (
        $limit !== '' &&
        (
            !ctype_digit($limit) ||
            (int)$limit < 1 ||
            (int)$limit > 10000000
        )
    ) {
        fail_save('Invalid ticket quantity limit.');
    }

    $benefitText = trim(
        (string)($benefits[$key] ?? '')
    );

    $items = array_values(
        array_filter(
            array_map(
                'trim',
                preg_split('/\R/u', $benefitText) ?: []
            ),
            static fn($item) => $item !== ''
        )
    );

    if (count($items) > 30) {
        fail_save('Too many benefits in one package.');
    }

    foreach ($items as $item) {
        if (mb_strlen($item) > 200) {
            fail_save('Package benefit is too long.');
        }
    }

    $validated[] = [
        'name' => $packageName,
        'description' => $packageDescription,
        'price' => $price,
        'benefits' => json_encode(
            $items,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ),
        'limit' => $limit === '' ? null : (int)$limit,
        'active' => isset($active[$key]) &&
                    $active[$key] === '1' ? 1 : 0
    ];
}

// Secure poster validation
$newPoster = null;
$target = null;

if (
    isset($_FILES['poster']) &&
    ($_FILES['poster']['error'] ?? UPLOAD_ERR_NO_FILE)
        !== UPLOAD_ERR_NO_FILE
) {
    $file = $_FILES['poster'];

    if (
        $file['error'] !== UPLOAD_ERR_OK ||
        $file['size'] > 3 * 1024 * 1024 ||
        $file['size'] < 1 ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        fail_save(
            'Poster upload failed or exceeds 3 MB.'
        );
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))
        ->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (
        !isset($allowed[$mime]) ||
        @getimagesize($file['tmp_name']) === false
    ) {
        fail_save(
            'Poster must be a valid JPG, PNG or WEBP image.'
        );
    }

    $newPoster = bin2hex(random_bytes(20)) .
        '.' . $allowed[$mime];

    $dir = __DIR__ . '/../storage/posters';

    if (!is_dir($dir) || !is_writable($dir)) {
        fail_save('Poster storage folder is not writable.');
    }

    $target = $dir . '/' . $newPoster;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        fail_save('Could not store the poster.');
    }
}

// Save event and packages in one transaction
try {
    $pdo->beginTransaction();

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'SELECT poster
             FROM events
             WHERE id = ?
             FOR UPDATE'
        );

        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            throw new RuntimeException(
                'Event does not exist.'
            );
        }

        $poster = $newPoster ?? $existing['poster'];

        $stmt = $pdo->prepare(
            'UPDATE events
             SET event_name = ?,
                 location = ?,
                 event_date = ?,
                 event_time = ?,
                 description = ?,
                 poster = ?,
                 status = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $name,
            $location,
            $date,
            $time,
            $description,
            $poster,
            $status,
            $id
        ]);

        // Development version: replaces package records.
        $stmt = $pdo->prepare(
            'DELETE FROM event_packages WHERE event_id = ?'
        );
        $stmt->execute([$id]);

    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO events
             (
                event_name,
                location,
                event_date,
                event_time,
                description,
                poster,
                status
             )
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $name,
            $location,
            $date,
            $time,
            $description,
            $newPoster,
            $status
        ]);

        $id = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare(
        'INSERT INTO event_packages
         (
            event_id,
            package_name,
            description,
            price,
            benefits,
            quantity_limit,
            is_active
         )
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($validated as $package) {
        $stmt->execute([
            $id,
            $package['name'],
            $package['description'],
            $package['price'],
            $package['benefits'],
            $package['limit'],
            $package['active']
        ]);
    }

    $pdo->commit();

    go('events.php');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($target && is_file($target)) {
        unlink($target);
    }

    error_log($e->getMessage());

    fail_save(
        'Database error. Check the server error log.'
    );
}
