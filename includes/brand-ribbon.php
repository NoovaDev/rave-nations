<?php
declare(strict_types=1);

$basePath = $basePath ?? '';

$tickerItems = [
    'LIVE ENTERTAINMENT',
    'ELECTRONIC MUSIC',
    'COASTAL ARENA STAGES',
    'RAVE CULTURE',
    'WORLD-CLASS PRODUCTION',
    'UNFORGETTABLE MOMENTS',
];
?>
<section class="rn-brand-strip" aria-label="Rave Nations Highlights">
    <div class="rn-brand-marquee">
        <div class="rn-brand-track">
            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                <?php foreach ($tickerItems as $index => $item): ?>
                    <div class="rn-brand-item <?= ($index % 2 === 1) ? 'is-outline' : '' ?>">
                        <a href="#events"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</section>