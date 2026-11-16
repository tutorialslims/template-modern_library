<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — halaman beranda
 * Susunan: hero → kategori → koleksi populer → banner promo → koleksi baru
 *          → pembaca terbaik → banner pencarian → lokasi
 */

$ml_stats = mlLibraryStats($dbs);

// Etalase hero: koleksi terbaru yang punya sampul
$ml_featured = mlFeaturedBooks($dbs, 5);

// Tautan daftar koleksi dengan urutan tertentu (pakai filter sort bawaan SLiMS)
$ml_url_latest  = mlSortedUrl('last-update');
$ml_url_popular = mlSortedUrl('most-loaned');

// Kategori DDC (ikon Font Awesome 5)
$ml_categories = [
  ['0', 'fas fa-desktop',    mlT('Karya Umum', 'General Works')],
  ['1', 'fas fa-lightbulb',          mlT('Filsafat', 'Philosophy')],
  ['2', 'fas fa-moon',         __('Religion')],
  ['3', 'fas fa-users',          mlT('Ilmu Sosial', 'Social Sciences')],
  ['4', 'fas fa-language',       __('Language')],
  ['5', 'fas fa-flask',          mlT('Sains', 'Pure Science')],
  ['6', 'fas fa-cogs',           mlT('Teknologi', 'Applied Sciences')],
  ['7', 'fas fa-paint-brush',        mlT('Seni & Rekreasi', 'Arts & Recreation')],
  ['8', 'fas fa-pencil-alt',    __('Literature')],
  ['9', 'fas fa-globe',     mlT('Sejarah & Geografi', 'History & Geography')],
];
?>

<?php include '_navbar.php'; ?>

<main class="ml-main">

    <!-- ================= HERO ================= -->
    <section class="container ml-section-first">
        <div class="ml-hero">
            <div class="ml-hero-text">
                <span class="ml-eyebrow"><i class="fas fa-leaf"></i> <?= $sysconf['library_name']; ?></span>
                <h1 class="ml-hero-title"><?= mlOpt($sysconf, 'ml_hero_title', 'Temukan Dunia Lewat Buku'); ?></h1>
                <p class="ml-hero-desc"><?= mlOpt($sysconf, 'ml_hero_subtitle', 'Jelajahi ribuan koleksi pustaka kami dalam satu katalog.'); ?></p>
                <div class="ml-hero-cta">
                    <a href="index.php?keywords=&search=search" class="ml-btn ml-btn-dark">
                        <?= mlT('Jelajahi Koleksi', 'Browse Collection') ?> <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                    <a href="index.php?p=member" class="ml-btn ml-btn-ghost"><?= __('Member Area') ?></a>
                </div>
                <ul class="ml-trust">
                    <li><i class="fas fa-book"></i><span><strong><?= mlShortNumber($ml_stats['biblio']) ?></strong><?= mlT('Judul Koleksi', 'Titles') ?></span></li>
                    <li><i class="fas fa-copy"></i><span><strong><?= mlShortNumber($ml_stats['item']) ?></strong><?= mlT('Eksemplar', 'Copies') ?></span></li>
                    <li><i class="fas fa-users"></i><span><strong><?= mlShortNumber($ml_stats['member']) ?></strong><?= mlT('Anggota Aktif', 'Active Members') ?></span></li>
                </ul>
            </div>
            <div class="ml-hero-visual">
                <?php if (!empty($ml_featured)) : ?>
                <!-- Etalase buku: sampul koleksi terbaru, berganti otomatis -->
                <div class="ml-showcase" id="ml-showcase">
                    <?php foreach ($ml_featured as $i => $book) :
                        $book_title = htmlspecialchars(strip_tags($book['title']), ENT_QUOTES, 'UTF-8', false);
                        ?>
                        <a class="ml-show-item<?= $i === 0 ? ' active' : '' ?>" href="index.php?p=show_detail&id=<?= $book['id'] ?>"
                           aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                            <span class="ml-show-bg" style="background-image:url('<?= $book['image'] ?>')"></span>
                            <span class="ml-show-cover">
                                <img src="<?= $book['image'] ?>" alt="<?= $book_title ?>" <?= $i === 0 ? '' : 'loading="lazy"' ?>>
                            </span>
                            <span class="ml-show-info">
                                <small><?= mlT('Koleksi terbaru', 'Latest arrival') ?></small>
                                <strong><?= $book_title ?></strong>
                                <?php if ($book['author'] !== '') : ?>
                                    <em><?= htmlspecialchars($book['author']) ?></em>
                                <?php endif; ?>
                                <span class="ml-show-go"><?= mlT('Lihat detail', 'View detail') ?> <i class="fas fa-arrow-right"></i></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="ml-hero-media" id="ml-hero-media" role="img" aria-label="<?= htmlspecialchars($sysconf['library_name']) ?>"></div>
                <?php endif; ?>
                <div class="ml-hero-badge">
                    <small><?= mlOpt($sysconf, 'ml_hero_badge_top', 'Koleksi'); ?></small>
                    <strong><?= mlOpt($sysconf, 'ml_hero_badge_main', 'BARU'); ?></strong>
                    <small><?= mlOpt($sysconf, 'ml_hero_badge_bottom', 'setiap bulan'); ?></small>
                </div>
                <div class="ml-hero-dots" id="ml-hero-dots"></div>
            </div>
        </div>
    </section>

    <!-- ================= KATEGORI ================= -->
    <section class="container ml-section">
        <ul class="ml-cats">
            <?php foreach ($ml_categories as [$code, $icon, $label]) : ?>
                <li>
                    <a href="index.php?callnumber=<?= $code ?>&search=search" title="<?= htmlspecialchars($label) ?>">
                        <span class="ml-cat-icon"><i class="<?= $icon ?>"></i></span>
                        <span class="ml-cat-label"><?= $label ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <li>
                <a href="javascript:void(0)" data-toggle="modal" data-target="#exampleModal">
                    <span class="ml-cat-icon ml-cat-icon-outline"><i class="fas fa-th-large"></i></span>
                    <span class="ml-cat-label"><?= mlT('Lihat Semua', 'View All') ?></span>
                </a>
            </li>
        </ul>
    </section>

    <div id="slims-home">

        <?php if ($sysconf['template']['classic_popular_collection']) : ?>
        <!-- ================= KOLEKSI POPULER ================= -->
        <section class="container ml-section">
            <div class="ml-panel">
                <div class="ml-section-head">
                    <h2 class="ml-section-title"><?= mlT('Koleksi Populer', 'Popular Picks') ?> <i class="fas fa-fire ml-accent"></i></h2>
                    <a class="ml-link" href="<?= $ml_url_popular ?>"><?= mlT('Lihat Semua', 'View All') ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <p class="ml-section-sub"><?= __('Our library\'s line of collection that have been favoured by our users were shown here. Look for them. Borrow them. Hope you also like them'); ?></p>
                <div class="ml-rail">
                    <?php
                    // Dirender di server (bukan API) agar tetap tampil walau belum ada peminjaman
                    $ml_popular = mlPopularBooks($dbs, (int)($sysconf['template']['classic_popular_collection_item'] ?? 6) ?: 6);
                    ?>
                    <?php if (empty($ml_popular)) : ?>
                        <div class="ml-empty"><i class="fas fa-book"></i> <?= mlT('Belum ada koleksi untuk ditampilkan.', 'No collections to show yet.') ?></div>
                    <?php else : ?>
                    <div class="collection ml-collection" v-pre>
                        <?php foreach ($ml_popular as $book) : ?>
                        <div class="ml-book">
                            <a class="ml-book-card" href="index.php?p=show_detail&id=<?= $book['id'] ?>">
                                <div class="ml-book-cover">
                                    <span class="ml-book-badge"><?= $book['loans'] > 0 ? mlT('Populer', 'Popular') : mlT('Pilihan', 'Pick') ?></span>
                                    <img src="<?= $book['image'] ?>" alt="" loading="lazy">
                                </div>
                                <div class="ml-book-body">
                                    <div class="ml-book-title"><?= htmlspecialchars(strip_tags($book['title']), ENT_QUOTES, 'UTF-8', false) ?></div>
                                    <div class="ml-book-foot">
                                        <span class="ml-book-more"><?= $book['loans'] > 0
                                            ? mlT('Dipinjam ', 'Borrowed ') . $book['loans'] . '×'
                                            : mlT('Lihat detail', 'View detail') ?></span>
                                        <span class="ml-book-go"><i class="fas fa-arrow-right"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <button type="button" class="ml-rail-btn ml-rail-prev" aria-label="Prev"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="ml-rail-btn ml-rail-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="ml-subjects">
                    <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ================= BANNER PROMO ================= -->
        <section class="container ml-section">
            <div class="ml-promos">
                <a class="ml-promo ml-promo-green" href="<?= $ml_url_latest ?>">
                    <div>
                        <span class="ml-promo-kicker"><?= mlT('Koleksi Baru', 'New Arrivals') ?></span>
                        <h3><?= mlT('Bacaan Segar Setiap Bulan', 'Fresh Reads Every Month') ?></h3>
                        <span class="ml-promo-link"><?= mlT('Lihat koleksi', 'See collection') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                    <span class="ml-promo-art"><i class="fas fa-book"></i></span>
                </a>
                <a class="ml-promo ml-promo-peach" href="index.php?p=member">
                    <div>
                        <span class="ml-promo-kicker"><?= __('Member Area') ?></span>
                        <h3><?= mlT('Simpan & Pinjam Favoritmu', 'Save & Borrow Your Favorites') ?></h3>
                        <span class="ml-promo-link"><?= mlT('Masuk sekarang', 'Sign in now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                    <span class="ml-promo-art"><i class="fas fa-bookmark"></i></span>
                </a>
                <a class="ml-promo ml-promo-blue" href="index.php?p=libinfo">
                    <div>
                        <span class="ml-promo-kicker"><?= __('Information') ?></span>
                        <h3><?= mlT('Kenali Layanan Perpustakaan', 'Discover Our Services') ?></h3>
                        <span class="ml-promo-link"><?= mlT('Selengkapnya', 'Learn more') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                    <span class="ml-promo-art"><i class="fas fa-university"></i></span>
                </a>
            </div>
        </section>

        <?php if ($sysconf['template']['classic_new_collection']) : ?>
        <!-- ================= KOLEKSI BARU ================= -->
        <section class="container ml-section" id="ml-new">
            <div class="ml-panel">
                <div class="ml-section-head">
                    <h2 class="ml-section-title"><?= __('New collections + updated'); ?> <i class="fas fa-leaf ml-accent"></i></h2>
                    <a class="ml-link" href="<?= $ml_url_latest ?>"><?= mlT('Lihat Semua', 'View All') ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <p class="ml-section-sub"><?= __('These are new collections list. Hope you like them. Maybe not all of them are new. But in term of time, we make sure that these are fresh from our processing oven'); ?></p>
                <div class="ml-rail">
                    <slims-collection url="index.php?p=api/biblio/latest" badge="<?= mlT('Baru', 'New') ?>"></slims-collection>
                    <button type="button" class="ml-rail-btn ml-rail-prev" aria-label="Prev"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="ml-rail-btn ml-rail-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="ml-subjects">
                    <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($sysconf['template']['classic_top_reader']) : ?>
        <!-- ================= PEMBACA TERBAIK ================= -->
        <section class="container ml-section">
            <div class="ml-section-head">
                <h2 class="ml-section-title"><?= __('Top reader of the year'); ?> <i class="fas fa-heart ml-heart"></i></h2>
                <span class="ml-muted"><?= __('Our best users, readers, so far. Continue to read if you want your name being mentioned here'); ?></span>
            </div>
            <div class="ml-readers">
                <slims-group-member url="index.php?p=api/member/top"></slims-group-member>
            </div>
        </section>
        <?php endif; ?>

    </div><!-- /#slims-home -->

    <!-- ================= BANNER PENCARIAN ================= -->
    <section class="container ml-section">
        <div class="ml-cta">
            <div class="ml-cta-icon"><i class="fas fa-search"></i></div>
            <div class="ml-cta-text">
                <h2><?= mlOpt($sysconf, 'ml_cta_title', 'Cari koleksi apa hari ini?'); ?></h2>
                <p><?= mlOpt($sysconf, 'ml_cta_subtitle', 'Ketik judul, pengarang, atau subjek.'); ?></p>
            </div>
            <div class="ml-cta-form">
                <form action="index.php" method="get">
                    <input type="hidden" name="search" value="search">
                    <input type="text" name="keywords" placeholder="<?= __('Enter keywords'); ?>" aria-label="<?= __('Enter keywords'); ?>">
                    <button type="submit"><?= __('Search'); ?></button>
                </form>
                <small><i class="fas fa-shield-alt mr-1"></i><?= mlT('Gratis untuk semua pengunjung. Tidak perlu login untuk mencari.', 'Free for every visitor. No login needed to search.') ?></small>
            </div>
        </div>
    </section>

    <?php if ($sysconf['template']['classic_map']) : ?>
    <!-- ================= LOKASI ================= -->
    <section class="container ml-section">
        <div class="ml-panel ml-location">
            <div class="ml-location-map">
                <iframe src="<?= $sysconf['template']['classic_map_link']; ?>" loading="lazy"
                        title="<?= htmlspecialchars($sysconf['library_name']) ?>" frameborder="0" allowfullscreen></iframe>
            </div>
            <div class="ml-location-info">
                <span class="ml-eyebrow"><i class="fas fa-map-marker-alt"></i> <?= mlT('Kunjungi Kami', 'Visit Us') ?></span>
                <h2 class="ml-section-title"><?= $sysconf['library_name']; ?></h2>
                <div class="ml-muted"><?= $sysconf['template']['classic_map_desc']; ?></div>
                <div class="ml-socials">
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_fb_link'] ?>" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_twitter_link'] ?>" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_youtube_link'] ?>" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_instagram_link'] ?>" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

</main>
