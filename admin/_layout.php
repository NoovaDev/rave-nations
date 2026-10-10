<?php
declare(strict_types=1);

if (!defined('APP_BASE')) {
    define('APP_BASE', '/rave');
}

function h(mixed $value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function admin_url(string $path): string
{
    return APP_BASE . '/admin/' . ltrim($path, '/');
}

function page_top(string $title, array $admin): void
{
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>
            <?= h($title) ?> | Rave Nations Admin
        </title>

        <link rel="stylesheet"
              href="<?= h(APP_BASE) ?>/style/admin.css">
    </head>

    <body>
        <div class="admin-layout">

            <aside class="sidebar">
                <div class="sidebar-brand">
                    <h2>RAVE NATIONS</h2>
                    <p>ADMIN PANEL</p>
                </div>

                <nav class="sidebar-nav">
                    <a href="<?= h(admin_url('dashboard.php')) ?>"
                       class="<?= $current === 'dashboard.php'
                           ? 'active' : '' ?>">
                        Dashboard
                    </a>

                    <a href="<?= h(admin_url('events.php')) ?>"
                       class="<?= $current === 'events.php'
                           ? 'active' : '' ?>">
                        All Events
                    </a>

                    <a href="<?= h(admin_url('event_form.php')) ?>"
                       class="<?= $current === 'event_form.php'
                           ? 'active' : '' ?>">
                        Create Event
                    </a>
                </nav>

                <div class="sidebar-bottom">
                    <p>
                        <?= h($admin['name'] ?? 'Administrator') ?>
                    </p>

                    <form method="POST"
                          action="<?= h(admin_url('logout.php')) ?>">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= h(csrf_token()) ?>">

                        <button type="submit" class="logout-btn">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <div class="main-area">

                <header class="admin-header">
                    <div>
                        <strong><?= h($title) ?></strong>
                    </div>

                    <div class="admin-profile">
                        <?= h($admin['name'] ?? 'Admin') ?>
                    </div>
                </header>

                <main class="admin-content">
    <?php
}

function page_bottom(): void
{
    ?>
                </main>
            </div>
        </div>
    </body>
    </html>
    <?php
}
