<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — halaman umum (detail, informasi, berita, bantuan, dll)
 */
include '_navbar.php';
$ml_is_detail = ($_GET['p'] ?? '') === 'show_detail';
?>
<main class="ml-main result-search">
    <?php if (!$ml_is_detail) : ?>
    <section class="container ml-section-first">
        <div class="ml-pagehead">
            <nav class="ml-crumb"><a href="index.php"><?= __('Home') ?></a> <i class="fas fa-chevron-right"></i> <span><?= strip_tags($page_title) ?></span></nav>
            <h1><?= $page_title ?></h1>
        </div>
    </section>
    <?php endif; ?>

    <section class="container <?= $ml_is_detail ? 'ml-section-first' : 'ml-section' ?>">
        <div class="ml-panel ml-content <?= $ml_is_detail ? 'ml-detail' : '' ?>">
            <?php
            if (!$ml_is_detail && ($_GET['p'] ?? '') === 'librarian') {
                echo '<div class="flex flex-row flex-wrap">' . $main_content . '</div>';
            } else {
                echo $main_content;
            }
            ?>
        </div>
    </section>
</main>
