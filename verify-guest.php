<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/database.php';

function rnEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function rnIcon(string $name, string $class = 'rn-icon'): string
{
    $icons = [
        'arrow-left'  => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
        'arrow-right' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
        'ticket'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
        'globe'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'id-card'     => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><line x1="14" y1="10" x2="18" y2="10"/><line x1="14" y1="14" x2="18" y2="14"/></svg>',
        'shield'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
    ];

    return $icons[$name] ?? '';
}

$packageId = isset($_GET['package_id']) ? (int) $_GET['package_id'] : 0;

if ($packageId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT ep.*, e.event_name, e.poster_path
    FROM event_packages ep
    JOIN events e ON ep.event_id = e.id
    WHERE ep.id = ?
    LIMIT 1
");
$stmt->execute([$packageId]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    http_response_code(404);
    exit('Invalid ticket package selected.');
}

$eventName = rnEscape($package['event_name']);
$packageName = rnEscape($package['package_name'] ?? 'Selected Ticket');
$ticketPrice = number_format((float) $package['ticket_price'], 2);
$heroBg = !empty($package['poster_path']) ? (string) $package['poster_path'] : 'assets/design/hero.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">
    <title>Guest Verification | Rave Nations</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style/verify-guest.css?v=175">
    <link rel="stylesheet" href="style/footer.css?v=76">
</head>

<body>

<main class="rn-verification-page">

    <!-- SIMPLE CENTERED HERO -->
    <section class="rn-verify-hero">
        <div class="rn-hero-background" style="background-image: url('<?= rnEscape($heroBg) ?>');"></div>
        <div class="rn-hero-overlay"></div>

        <div class="rn-hero-container">

            <div class="rn-topline">
                <a href="event.php?id=<?= (int) $package['event_id'] ?>" class="rn-verification-back">
                    <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
                    <span>Back to tickets</span>
                </a>

                <span class="rn-step-badge">STEP 01 / 03</span>
            </div>

            <div class="rn-verification-heading">
                <span class="rn-verification-eyebrow">GUEST VERIFICATION</span>

                <h1><?= $eventName ?></h1>

                <div class="rn-hero-meta-row">
                    <span class="rn-hero-chip">
                        <?= rnIcon('ticket', 'rn-icon rn-icon-sm') ?>
                        <span><?= $packageName ?></span>
                    </span>

                    <span class="rn-hero-chip">
                        <span>LKR <?= $ticketPrice ?></span>
                    </span>
                </div>

                <p>Select your country of residence and enter your ID for fast venue gate entry.</p>
            </div>

        </div>
    </section>

    <!-- SIMPLE CLEAN FORM BODY -->
    <section class="rn-verify-body">
        <div class="rn-verification-wrapper">

            <div class="rn-verification-card">
                <div class="rn-card-header">
                    <span class="rn-card-eyebrow">BOOKING · STEP 01</span>
                    <h2>Verify Your <span>Identity</span></h2>
                </div>

                <form action="addons.php" method="GET" class="rn-guest-section">
                    <input type="hidden" name="package_id" value="<?= $packageId ?>">
                    <input type="hidden" name="guest_type" id="guest_type_input" value="local">

                    <div class="rn-form-group">
                        <label for="nationality_select">
                            <?= rnIcon('globe', 'rn-icon rn-icon-sm') ?>
                            <span>Nationality / Country of Residence *</span>
                        </label>
                        <select name="nationality" id="nationality_select" class="rn-form-control" required>
                            <option value="Sri Lanka" selected>Sri Lanka (Local Guest)</option>
                            <option value="Australia">Australia</option>
                            <option value="Canada">Canada</option>
                            <option value="France">France</option>
                            <option value="Germany">Germany</option>
                            <option value="India">India</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Other">Other International Country</option>
                        </select>
                    </div>

                    <div class="rn-form-group" id="id_field_group">
                        <label for="id_number_input" id="id_label">
                            <?= rnIcon('id-card', 'rn-icon rn-icon-sm') ?>
                            <span id="id_label_text">Sri Lankan NIC Number *</span>
                        </label>
                        <input
                            type="text"
                            name="nic_passport"
                            id="id_number_input"
                            class="rn-form-control"
                            placeholder="e.g., 199512345678 or 951234567V"
                            required
                        >
                        <small class="rn-field-hint">
                            Used to verify your pass at the gate and match partner pricing.
                        </small>
                    </div>

                    <button type="submit" class="rn-submit-btn">
                        <span>Continue to Experiences</span>
                        <?= rnIcon('arrow-right', 'rn-icon rn-icon-sm') ?>
                    </button>
                </form>

                <div class="rn-verification-footer">
                    <span class="rn-security-icon">
                        <?= rnIcon('shield', 'rn-icon rn-icon-sm') ?>
                    </span>
                    <p>Your ID details are encrypted and used only for venue entry verification.</p>
                </div>
            </div>

        </div>
    </section>

</main>

<!-- Footer Component -->
<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    require_once __DIR__ . '/includes/footer.php';
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('nationality_select');
    const guestTypeInput = document.getElementById('guest_type_input');
    const idLabelText = document.getElementById('id_label_text');
    const idInput = document.getElementById('id_number_input');

    function syncGuestFields() {
        if (select.value === 'Sri Lanka') {
            guestTypeInput.value = 'local';
            idLabelText.textContent = 'Sri Lankan NIC Number *';
            idInput.placeholder = 'e.g., 199512345678 or 951234567V';
        } else {
            guestTypeInput.value = 'foreign';
            idLabelText.textContent = 'Passport / Govt ID Number *';
            idInput.placeholder = 'e.g., P123456789';
        }
    }

    select.addEventListener('change', syncGuestFields);
    syncGuestFields();
});
</script>

</body>
</html>