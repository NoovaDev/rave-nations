<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_layout.php';

$admin = require_admin($pdo);

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$event = [
    'id' => 0,
    'event_name' => '',
    'location' => '',
    'event_date' => '',
    'event_time' => '19:00',
    'description' => '',
    'poster' => '',
    'status' => 'draft'
];

$packages = [];

if ($id) {
    $stmt = $pdo->prepare(
        'SELECT * FROM events WHERE id = ?'
    );
    $stmt->execute([$id]);

    $event = $stmt->fetch();

    if (!$event) {
        http_response_code(404);
        exit('Event not found.');
    }

    $stmt = $pdo->prepare(
        'SELECT *
         FROM event_packages
         WHERE event_id = ?
         ORDER BY id'
    );

    $stmt->execute([$id]);
    $packages = $stmt->fetchAll();
}

page_top($id ? 'Edit Event' : 'Create Event', $admin);
?>

<div class="heading">
    <div>
        <p class="eyebrow">GUIDED EVENT SETUP</p>

        <h1>
            <?= $id ? 'Edit Your Event' : 'Create New Event' ?>
        </h1>

        <p class="muted">
            Complete the three steps to manage
            your event and ticket packages.
        </p>
    </div>

    <a class="btn ghost"
       href="<?= h(admin_url('events.php')) ?>">
        ← All Events
    </a>
</div>

<div class="wizard-shell">

    <div class="wizard-progress">
        <div class="wizard-progress-bar"
             id="wizardProgress"></div>
    </div>

    <ol class="steps" aria-label="Event setup progress">
        <li class="active" data-step-label="0">
            <span class="step-number">1</span>
            <div>
                <strong>Event Details</strong>
                <small>Name, date and poster</small>
            </div>
        </li>

        <li data-step-label="1">
            <span class="step-number">2</span>
            <div>
                <strong>Ticket Packages</strong>
                <small>Prices and benefits</small>
            </div>
        </li>

        <li data-step-label="2">
            <span class="step-number">3</span>
            <div>
                <strong>Review & Save</strong>
                <small>Confirm everything</small>
            </div>
        </li>
    </ol>

    <form
        id="eventWizard"
        action="<?= h(admin_url('event_save.php')) ?>"
        method="POST"
        enctype="multipart/form-data"
        novalidate
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= h(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="id"
            value="<?= (int)$event['id'] ?>"
        >

        <!-- STEP 1: EVENT DETAILS -->

        <section class="panel wizard-step"
                 data-step="0">

            <div class="section-title">
                <span class="section-icon">01</span>

                <div>
                    <h2>Event Information</h2>
                    <p class="muted">
                        Enter the details customers
                        will see on the event page.
                    </p>
                </div>
            </div>

            <div class="form-grid">

                <label>
                    Event Name
                    <span class="required">*</span>

                    <input
                        type="text"
                        name="event_name"
                        maxlength="200"
                        placeholder="Rave Nations Midnight Festival"
                        value="<?= h($event['event_name']) ?>"
                        required
                    >
                </label>

                <label>
                    Location / Venue
                    <span class="required">*</span>

                    <input
                        type="text"
                        name="location"
                        maxlength="255"
                        placeholder="Colombo, Sri Lanka"
                        value="<?= h($event['location']) ?>"
                        required
                    >
                </label>

                <label>
                    Event Date
                    <span class="required">*</span>

                    <input
                        type="date"
                        name="event_date"
                        value="<?= h($event['event_date']) ?>"
                        required
                    >
                </label>

                <label>
                    Event Start Time
                    <span class="required">*</span>

                    <input
                        type="time"
                        name="event_time"
                        value="<?= h(substr(
                            (string)$event['event_time'],
                            0,
                            5
                        )) ?>"
                        required
                    >
                </label>

                <label class="full">
                    Event Poster

                    <span class="muted small">
                        JPG, PNG or WEBP — Maximum 3 MB
                    </span>

                    <input
                        id="posterInput"
                        type="file"
                        name="poster"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <span class="field-hint"
                          id="posterFileName">
                        <?= $event['poster']
                            ? 'Existing poster will be kept unless you upload a new one.'
                            : 'Choose a poster image from your computer.' ?>
                    </span>
                </label>

                <label class="full">
                    Short Event Description

                    <textarea
                        name="description"
                        rows="4"
                        maxlength="2000"
                        placeholder="Describe the event..."
                    ><?= h($event['description']) ?></textarea>
                </label>

                <label>
                    Publication Status

                    <select name="status">
                        <option
                            value="draft"
                            <?= $event['status'] === 'draft'
                                ? 'selected' : '' ?>
                        >
                            Draft — Keep Private
                        </option>

                        <option
                            value="published"
                            <?= $event['status'] === 'published'
                                ? 'selected' : '' ?>
                        >
                            Published
                        </option>
                    </select>
                </label>

            </div>
        </section>

        <!-- STEP 2: PACKAGES -->

        <section
            class="panel wizard-step"
            data-step="1"
            hidden
        >

            <div class="section-title">
                <span class="section-icon">02</span>

                <div>
                    <h2>Ticket Packages</h2>
                    <p class="muted">
                        Add General, VIP, VVIP or
                        custom ticket packages.
                        Enter one benefit per line.
                    </p>
                </div>
            </div>

            <div id="packageRows"></div>

            <button
                type="button"
                class="btn add-package"
                id="addPackage"
            >
                + Add Another Package
            </button>

        </section>

        <!-- STEP 3: REVIEW -->

        <section
            class="panel wizard-step"
            data-step="2"
            hidden
        >

            <div class="section-title">
                <span class="section-icon">03</span>

                <div>
                    <h2>Review Your Event</h2>
                    <p class="muted">
                        Check the event information
                        and packages before saving.
                    </p>
                </div>
            </div>

            <div id="reviewContent"
                 aria-live="polite"></div>

            <div class="notice">
                You can edit your event later.
                Package references must be preserved
                before connecting customer bookings.
            </div>

        </section>

        <!-- WIZARD NAVIGATION -->

        <div class="wizard-actions">

            <button
                type="button"
                class="btn ghost"
                id="prevStep"
                hidden
            >
                ← Back
            </button>

            <span class="muted small" id="stepCounter">
                Step 1 of 3
            </span>

            <button
                type="button"
                class="btn"
                id="nextStep"
            >
                Continue →
            </button>

            <button
                type="submit"
                class="btn"
                id="saveEvent"
                hidden
            >
                Save Event
            </button>

        </div>

    </form>
</div>

<?php
$initialPackages = array_map(
    static function (array $package): array {
        return [
            'name' => $package['package_name'],
            'description' => $package['description'],
            'price' => $package['price'],
            'benefits' => implode(
                "\n",
                json_decode(
                    $package['benefits'],
                    true
                ) ?: []
            ),
            'limit' => $package['quantity_limit'],
            'active' => $package['is_active']
        ];
    },
    $packages
);
?>

<script
    id="initialPackages"
    type="application/json"
><?= json_encode(
    $initialPackages,
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
) ?></script>

<script
    src="<?= h(APP_BASE) ?>/assets/js/admin-wizard.js"
    defer
></script>

<?php page_bottom(); ?>
