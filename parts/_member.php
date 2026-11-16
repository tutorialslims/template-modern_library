<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — area anggota
 */
include '_navbar.php';
?>
<?php if ($is_login) : ?>
    <main class="ml-main member-area">
        <section class="container ml-section-first">
            <div class="ml-pagehead">
                <nav class="ml-crumb"><a href="index.php"><?= __('Home') ?></a> <i class="fas fa-chevron-right"></i> <span><?= __('Member Area') ?></span></nav>
                <h1><?= mlT('Halo', 'Hello') ?>, <?= htmlspecialchars($_SESSION['m_name']) ?></h1>
            </div>
        </section>
        <section class="ml-section ml-member-content">
            <?= $main_content; ?>
        </section>
    </main>
<?php else : ?>
    <main class="ml-main result-search page-member-area">
        <section class="container ml-section-first">
            <div class="ml-login-wrap">
                <div class="ml-login-aside">
                    <span class="ml-eyebrow"><i class="fas fa-leaf"></i> <?= __('Member Area') ?></span>
                    <h1><?= mlT('Masuk ke akun anggota', 'Sign in to your member account') ?></h1>
                    <p><?= mlT('Pantau peminjaman, simpan bookmark, dan pesan koleksi favorit Anda dari satu tempat.', 'Track your loans, save bookmarks and reserve your favorite titles in one place.') ?></p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> <?= mlT('Riwayat peminjaman', 'Loan history') ?></li>
                        <li><i class="fas fa-check-circle"></i> <?= mlT('Bookmark & keranjang koleksi', 'Bookmarks & title basket') ?></li>
                        <li><i class="fas fa-check-circle"></i> <?= mlT('Reservasi koleksi', 'Title reservation') ?></li>
                    </ul>
                </div>
                <div class="ml-login-card">
                    <?= $main_content; ?>
                </div>
            </div>
        </section>
    </main>
<?php endif; ?>
