<?php
declare(strict_types=1);

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
        'arrow-down'  => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>',
        'arrow-up-r'  => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>',
        'ticket'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
        'globe'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'check'       => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
        'sparkle'     => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3z"/></svg>',
    ];

    return $icons[$name] ?? '';
}

function rnCalcOriginalPrice(float $finalPrice, ?string $discountText, mixed $explicitOriginal = null): ?float
{
    if (is_numeric($explicitOriginal) && (float) $explicitOriginal > $finalPrice) {
        return (float) $explicitOriginal;
    }

    $text = trim((string) $discountText);
    if ($text === '') {
        return null;
    }

    if (preg_match('/save\s*(?:lkr|rs\.?)?\s*([0-9,]+(?:\.[0-9]+)?)/i', $text, $m)) {
        $saved = (float) str_replace(',', '', $m[1]);
        if ($saved > 0) {
            return $finalPrice + $saved;
        }
    }

    if (preg_match('/(\d+(?:\.\d+)?)\s*%/i', $text, $m)) {
        $pct = (float) $m[1];
        if ($pct > 0 && $pct < 100) {
            return round($finalPrice / (1 - ($pct / 100)), 2);
        }
    }

    return null;
}

$packageId   = isset($_GET['package_id']) ? (int) $_GET['package_id'] : 0;
$guestType   = isset($_GET['guest_type']) ? $_GET['guest_type'] : 'local';
$nicPassport = trim($_GET['nic_passport'] ?? '');

if (!in_array($guestType, ['local', 'foreign'], true)) {
    $guestType = 'local';
}

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
$mainPackage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mainPackage) {
    http_response_code(404);
    exit('Selected ticket pass not found.');
}

$partnerStmt = $pdo->prepare("
    SELECT
        p.id AS partner_id,
        p.partner_name,
        p.description AS partner_desc,
        p.poster_path AS partner_poster,
        pp.id AS addon_id,
        pp.package_name,
        pp.benefits,
        pp.price_lkr,
        pp.price_usd,
        pp.discount_text,
        pp.guest_type
    FROM event_partners p
    LEFT JOIN partner_packages pp
        ON p.id = pp.partner_id
        AND pp.guest_type = ?
    WHERE p.event_id = ?
    ORDER BY p.id ASC, pp.id ASC
");
$partnerStmt->execute([
    $guestType,
    $mainPackage['event_id']
]);
$rawPartners = $partnerStmt->fetchAll(PDO::FETCH_ASSOC);

$partners = [];
$totalOptions = 0;

foreach ($rawPartners as $row) {
    $partnerId = $row['partner_id'];

    if (!isset($partners[$partnerId])) {
        $partners[$partnerId] = [
            'id'          => (int) $partnerId,
            'name'        => $row['partner_name'],
            'description' => $row['partner_desc'],
            'poster'      => trim((string) ($row['partner_poster'] ?? '')),
            'packages'    => []
        ];
    }

    if (!empty($row['addon_id'])) {
        $totalOptions++;
        $finalLkr = (float) $row['price_lkr'];
        $originalLkr = rnCalcOriginalPrice($finalLkr, $row['discount_text'] ?? null, $row['original_price'] ?? null);

        $partners[$partnerId]['packages'][] = [
            'id'           => (int) $row['addon_id'],
            'name'         => $row['package_name'],
            'benefits'     => $row['benefits'],
            'lkr'          => $finalLkr,
            'original_lkr' => $originalLkr,
            'usd'          => !empty($row['price_usd']) ? (float) $row['price_usd'] : null,
            'discount'     => $row['discount_text']
        ];
    }
}

$eventName = rnEscape($mainPackage['event_name']);
$ticketName = rnEscape($mainPackage['package_name']);
$mainTicketPrice = (float) $mainPackage['ticket_price'];
$eventPoster = trim((string) ($mainPackage['poster_path'] ?? ''));
$heroBg = $eventPoster !== '' ? $eventPoster : 'assets/design/hero.jpg';

$skipUrl = 'checkout.php?' . http_build_query([
    'package_id'   => $packageId,
    'guest_type'   => $guestType,
    'nic_passport' => $nicPassport,
]);

$pageTitle = 'Event Experiences | ' . $eventName;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">

    <title><?= $pageTitle ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style/addons.css?v=220">
    <link rel="stylesheet" href="style/footer.css?v=76">
</head>

<body>

<main class="rn-addons-page">

    <!-- SIMPLE HERO -->
    <section class="rn-addons-hero">
        <div class="rn-hero-background" style="background-image: url('<?= rnEscape($heroBg) ?>');"></div>
        <div class="rn-hero-overlay"></div>

        <div class="rn-hero-container">

            <div class="rn-topline">
                <a href="event.php?id=<?= (int) $mainPackage['event_id'] ?>" class="rn-back-link">
                    <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
                    <span>Back to event</span>
                </a>

                <a href="verify-guest.php?package_id=<?= $packageId ?>" class="rn-guest-badge">
                    <?= rnIcon('globe', 'rn-icon rn-icon-sm') ?>
                    <span><?= $guestType === 'foreign' ? 'International Guest' : 'Local Guest' ?></span>
                    <span class="rn-guest-change">Change</span>
                </a>
            </div>

            <div class="rn-addons-header">
                <span class="rn-step-label">BOOKING · STEP 02</span>

                <h1><?= $eventName ?></h1>

                <div class="rn-hero-meta-row">
                    <span class="rn-hero-chip">
                        <?= rnIcon('ticket', 'rn-icon rn-icon-sm') ?>
                        <span><?= $ticketName ?></span>
                    </span>

                    <span class="rn-hero-chip">
                        <span>LKR <?= number_format($mainTicketPrice, 2) ?></span>
                    </span>
                </div>

                <p>Add an optional partner experience to enjoy alongside your event pass.</p>
            </div>

            <a href="#experiences" class="rn-hero-scroll">
                <span>EXPLORE EXPERIENCES</span>
                <?= rnIcon('arrow-down', 'rn-icon rn-icon-sm') ?>
            </a>

        </div>
    </section>

    <!-- MINI HOMEPAGE-STYLE POSTER CARDS + MANUAL ARROW LOOP -->
    <section class="rn-addons-body" id="experiences">
        <div class="rn-container">

            <div class="rn-section-heading">
                <div>
                    <span class="rn-section-eyebrow">BOOK YOUR EXPERIENCE</span>
                    <h2>Available <span>Experiences</span></h2>
                </div>

                <span class="rn-package-count">
                    <?= $totalOptions ?>
                    <?= $totalOptions === 1 ? 'EXPERIENCE OPTION' : 'EXPERIENCE OPTIONS' ?>
                </span>
            </div>

            <form action="checkout.php" method="get" id="addonsForm">

                <input type="hidden" name="package_id" value="<?= $packageId ?>">
                <input type="hidden" name="guest_type" value="<?= rnEscape($guestType) ?>">
                <input type="hidden" name="nic_passport" value="<?= rnEscape($nicPassport) ?>">

                <?php if (empty($partners)): ?>

                    <div class="rn-empty-state">
                        <div class="rn-empty-symbol">
                            <?= rnIcon('sparkle') ?>
                        </div>
                        <h2>No experiences available</h2>
                        <p>
                            There are currently no partner packages available for this event. You can continue straight to checkout.
                        </p>
                    </div>

                <?php else: ?>

                    <?php foreach ($partners as $partner):
                        $cardPoster = $partner['poster'] !== '' ? $partner['poster'] : $eventPoster;
                        $partnerPackages = $partner['packages'];
                        $pkgCount = count($partnerPackages);
                        $carouselId = 'carousel-' . (int) $partner['id'];
                    ?>

                        <section class="rn-partner-section">

                            <div class="rn-partner-heading">
                                <div>
                                    <span class="rn-partner-caption">OFFICIAL PARTNER</span>
                                    <h3><?= rnEscape($partner['name']) ?></h3>

                                    <?php if (!empty($partner['description'])): ?>
                                        <p><?= rnEscape($partner['description']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <?php if ($pkgCount > 1): ?>
                                    <div class="rn-carousel-controls" aria-label="<?= rnEscape($partner['name']) ?> Controls">
                                        <span class="rn-carousel-counter" data-counter-for="<?= $carouselId ?>">
                                            1 / <?= $pkgCount ?>
                                        </span>
                                        <button type="button" class="rn-loop-btn" data-prev-for="<?= $carouselId ?>" aria-label="Previous experience">
                                            <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
                                        </button>
                                        <button type="button" class="rn-loop-btn" data-next-for="<?= $carouselId ?>" aria-label="Next experience">
                                            <?= rnIcon('arrow-right', 'rn-icon rn-icon-sm') ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($partnerPackages)): ?>

                                <p class="rn-no-packages">
                                    No packages available in this category.
                                </p>

                            <?php else: ?>

                                <div class="rn-carousel-viewport" id="<?= $carouselId ?>">
                                    <div class="rn-package-list">

                                        <?php foreach ($partnerPackages as $pkg): ?>

                                            <label class="rn-package-card">

                                                <?php if ($cardPoster !== ''): ?>
                                                    <img
                                                        class="rn-package-card-bg"
                                                        src="<?= rnEscape($cardPoster) ?>"
                                                        alt="<?= rnEscape($pkg['name']) ?>"
                                                        loading="lazy"
                                                        decoding="async"
                                                        draggable="false"
                                                    >
                                                <?php endif; ?>

                                                <div class="rn-package-card-shade"></div>

                                                <?php if (!empty($pkg['discount'])): ?>
                                                    <span class="rn-discount"><?= rnEscape($pkg['discount']) ?></span>
                                                <?php endif; ?>

                                                <div class="rn-package-overlay">
                                                    <h4><?= rnEscape($pkg['name']) ?></h4>

                                                    <?php if (!empty($pkg['benefits'])): ?>
                                                        <p class="rn-package-benefits">
                                                            <?= nl2br(rnEscape($pkg['benefits'])) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if ($guestType === 'foreign' && !empty($pkg['usd'])): ?>
                                                        <p class="rn-usd-price">
                                                            Approx. USD $<?= number_format($pkg['usd'], 2) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <div class="rn-package-footer">
                                                        <div class="rn-price-stack">
                                                            <?php if (!empty($pkg['original_lkr'])): ?>
                                                                <del class="rn-original-price">
                                                                    LKR <?= number_format($pkg['original_lkr'], 2) ?>
                                                                </del>
                                                            <?php endif; ?>

                                                            <strong class="rn-final-price">
                                                                <small>LKR</small>
                                                                <?= number_format($pkg['lkr'], 2) ?>
                                                            </strong>
                                                        </div>

                                                        <span class="rn-package-select">
                                                            <input
                                                                type="checkbox"
                                                                name="addons[]"
                                                                value="<?= (int) $pkg['id'] ?>"
                                                                data-price="<?= rnEscape($pkg['lkr']) ?>"
                                                                class="rn-addon-checkbox"
                                                            >

                                                            <span class="rn-select-ui">
                                                                <span class="rn-select-tick"><?= rnIcon('check', 'rn-icon rn-icon-sm') ?></span>
                                                                <span class="rn-select-label">Select</span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>

                                            </label>

                                        <?php endforeach; ?>

                                    </div>
                                </div>

                            <?php endif; ?>

                        </section>

                    <?php endforeach; ?>

                <?php endif; ?>

                <!-- Bottom Fixed Checkout Dock -->
                <div class="rn-checkout-bar">
                    <div class="rn-checkout-total">
                        <div class="rn-checkout-meta-row">
                            <span class="rn-checkout-total-label">YOUR TOTAL</span>
                            <span class="rn-selected-pill" id="selectedCountPill">0 add-ons</span>
                        </div>
                        <strong id="liveTotalPrice">
                            LKR <?= number_format($mainTicketPrice, 2) ?>
                        </strong>
                    </div>

                    <div class="rn-checkout-actions">
                        <a href="<?= rnEscape($skipUrl) ?>" class="rn-skip-link">
                            Skip
                        </a>

                        <button type="submit" class="rn-checkout-button">
                            <span>Continue</span>
                            <?= rnIcon('arrow-up-r', 'rn-icon rn-icon-sm') ?>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </section>

</main>

<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    require_once __DIR__ . '/includes/footer.php';
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* 1. Live Total Calculation */
    const mainPrice = <?= json_encode($mainTicketPrice) ?>;
    const checkboxes = document.querySelectorAll('.rn-addon-checkbox');
    const totalDisplay = document.getElementById('liveTotalPrice');
    const countPill = document.getElementById('selectedCountPill');

    function updateTotal() {
        let total = mainPrice;
        let selectedCount = 0;

        checkboxes.forEach(function (checkbox) {
            const card = checkbox.closest('.rn-package-card');

            if (checkbox.checked) {
                total += Number(checkbox.dataset.price) || 0;
                selectedCount += 1;
                if (card) card.classList.add('selected');
            } else {
                if (card) card.classList.remove('selected');
            }
        });

        totalDisplay.textContent = 'LKR ' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        if (countPill) {
            countPill.textContent = selectedCount === 1
                ? '1 add-on'
                : selectedCount + ' add-ons';
        }
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateTotal);
    });

    updateTotal();

    /* 2. Manual Infinite Arrow Loop (No Auto-Scroll) */
    document.querySelectorAll('.rn-carousel-viewport').forEach(function (viewport) {
        const track = viewport.querySelector('.rn-package-list');
        if (!track) return;

        const cardsCount = track.children.length;
        if (cardsCount <= 1) return;

        const id = viewport.id;
        const prevBtn = document.querySelector('[data-prev-for="' + id + '"]');
        const nextBtn = document.querySelector('[data-next-for="' + id + '"]');
        const counterEl = document.querySelector('[data-counter-for="' + id + '"]');

        let currentIndex = 0;
        let isAnimating = false;

        function updateCounter() {
            if (counterEl) {
                counterEl.textContent = (currentIndex + 1) + ' / ' + cardsCount;
            }
        }

        function getStepWidth() {
            const firstCard = track.firstElementChild;
            if (!firstCard) return 0;
            const style = window.getComputedStyle(track);
            const gap = parseFloat(style.columnGap || style.gap || '14') || 14;
            return firstCard.getBoundingClientRect().width + gap;
        }

        function canSlideWithoutGap(step) {
            return (track.scrollWidth - step) >= (viewport.clientWidth - 2);
        }

        function goNext() {
            if (isAnimating) return;
            const step = getStepWidth();

            currentIndex = (currentIndex + 1) % cardsCount;
            updateCounter();

            if (!canSlideWithoutGap(step)) {
                isAnimating = true;
                track.style.transition = 'opacity 0.16s ease';
                track.style.opacity = '0.55';
                setTimeout(() => {
                    track.appendChild(track.firstElementChild);
                    track.style.opacity = '1';
                    setTimeout(() => {
                        track.style.transition = 'none';
                        isAnimating = false;
                    }, 160);
                }, 140);
                return;
            }

            isAnimating = true;
            track.style.transition = 'transform 0.32s cubic-bezier(0.22, 1, 0.36, 1)';
            track.style.transform = `translate3d(-${step}px, 0, 0)`;

            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                track.style.transition = 'none';
                track.appendChild(track.firstElementChild);
                track.style.transform = 'translate3d(0, 0, 0)';
                void track.offsetHeight;
                isAnimating = false;
            };

            track.addEventListener('transitionend', finish, { once: true });
            setTimeout(finish, 360);
        }

        function goPrev() {
            if (isAnimating) return;
            const step = getStepWidth();

            currentIndex = (currentIndex - 1 + cardsCount) % cardsCount;
            updateCounter();

            if (!canSlideWithoutGap(step)) {
                isAnimating = true;
                track.style.transition = 'opacity 0.16s ease';
                track.style.opacity = '0.55';
                setTimeout(() => {
                    track.insertBefore(track.lastElementChild, track.firstElementChild);
                    track.style.opacity = '1';
                    setTimeout(() => {
                        track.style.transition = 'none';
                        isAnimating = false;
                    }, 160);
                }, 140);
                return;
            }

            isAnimating = true;
            track.style.transition = 'none';
            track.insertBefore(track.lastElementChild, track.firstElementChild);
            track.style.transform = `translate3d(-${step}px, 0, 0)`;
            void track.offsetHeight;

            track.style.transition = 'transform 0.32s cubic-bezier(0.22, 1, 0.36, 1)';
            track.style.transform = 'translate3d(0, 0, 0)';

            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                track.style.transition = 'none';
                isAnimating = false;
            };

            track.addEventListener('transitionend', finish, { once: true });
            setTimeout(finish, 360);
        }

        if (nextBtn) nextBtn.addEventListener('click', goNext);
        if (prevBtn) prevBtn.addEventListener('click', goPrev);

        /* Mobile Touch Swipe Support */
        let touchStartX = 0;
        viewport.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });

        viewport.addEventListener('touchend', function (e) {
            const diff = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(diff) > 40) {
                if (diff < 0) goNext();
                else goPrev();
            }
        }, { passive: true });
    });
});
</script>

</body>
</html>