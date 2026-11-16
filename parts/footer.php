<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — footer
 */
?>

<!-- ================= STRIP FITUR ================= -->
<section class="container ml-section">
    <div class="ml-features">
        <div class="ml-feature">
            <i class="fas fa-book"></i>
            <div><strong><?= mlT('Peminjaman Mudah', 'Easy Borrowing') ?></strong><span><?= mlT('Pinjam langsung di meja sirkulasi', 'Borrow at the circulation desk') ?></span></div>
        </div>
        <div class="ml-feature">
            <i class="fas fa-globe"></i>
            <div><strong><?= mlT('Katalog 24/7', '24/7 Catalog') ?></strong><span><?= mlT('Cari koleksi dari mana saja', 'Search from anywhere') ?></span></div>
        </div>
        <div class="ml-feature">
            <i class="fas fa-id-card"></i>
            <div><strong><?= __('Member Area') ?></strong><span><?= mlT('Riwayat pinjam & bookmark', 'Loans & bookmarks') ?></span></div>
        </div>
        <div class="ml-feature">
            <i class="fas fa-headphones"></i>
            <div><strong><?= mlT('Bantuan Pustakawan', 'Librarian Help') ?></strong><span><?= mlT('Siap membantu Anda', 'Here to help you') ?></span></div>
        </div>
    </div>
</section>

<footer class="ml-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <a class="ml-brand ml-brand-light" href="index.php">
                    <span class="ml-brand-logo">
                    <?php
                    if (isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && $imagesDisk->isExists($path = 'default/' . $sysconf['logo_image'])) {
                        echo '<img src="' . SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=200" alt="logo">';
                    } elseif (file_exists(__DIR__ . '/../assets/images/logo.png')) {
                        echo '<img src="' . assets('images/logo.png') . '" alt="logo">';
                    } else { ?>
                        <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <path d="M14 18h20l-2 22H16L14 18z" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/>
                            <path d="M19 18v-3a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M24 36c0-6 3-9 6-10-1 5-3 8-6 10zm0 0c0-5-2-8-5-9 0 4 2 7 5 9z" fill="currentColor"/>
                        </svg>
                    <?php } ?>
                    </span>
                    <span class="ml-brand-text">
                        <strong><?= $sysconf['library_name']; ?></strong>
                        <small><?= $sysconf['library_subname']; ?></small>
                    </span>
                </a>
                <div class="ml-footer-about"><?= $sysconf['template']['classic_footer_about_us']; ?></div>
            </div>
            <div class="col-lg-2 col-md-6 col-6 mb-4">
                <h4><?= mlT('Jelajahi', 'Explore') ?></h4>
                <ul>
                    <li><a href="index.php"><?= __('Home') ?></a></li>
                    <li><a href="index.php?keywords=&search=search"><?= mlT('Semua Koleksi', 'All Collections') ?></a></li>
                    <li><a href="index.php?p=news"><?= __('News') ?></a></li>
                    <li><a href="javascript:void(0)" data-toggle="modal" data-target="#exampleModal"><?= mlT('Kategori', 'Categories') ?></a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 col-6 mb-4">
                <h4><?= mlT('Layanan', 'Services') ?></h4>
                <ul>
                    <li><a href="index.php?p=libinfo"><?= __('Information') ?></a></li>
                    <?php if (mlContentExists($dbs, 'services')) : ?>
                    <li><a href="index.php?p=services"><?= __('Services') ?></a></li>
                    <?php else : ?>
                    <li><a href="index.php?p=help"><?= __('Help') ?></a></li>
                    <?php endif; ?>
                    <li><a href="index.php?p=librarian"><?= __('Librarian') ?></a></li>
                    <li><a href="index.php?p=member"><?= __('Member Area') ?></a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <h4><?= __('Search'); ?></h4>
                <p class="ml-footer-muted"><?= __('start it by typing one or more keywords for title, author or subject'); ?></p>
                <form action="index.php" class="ml-footer-search">
                    <input type="hidden" ref="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input name="keywords" type="text" placeholder="<?= __('Enter keywords'); ?>" aria-label="<?= __('Enter keywords'); ?>">
                    <button type="submit" value="search" name="search" aria-label="<?= __('Find Collection'); ?>"><i class="fas fa-search"></i></button>
                </form>
                <div class="ml-socials ml-socials-light">
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_fb_link'] ?>" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_twitter_link'] ?>" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_youtube_link'] ?>" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_instagram_link'] ?>" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="ml-footer-bottom">
            <span>&copy; <?= date('Y'); ?> <?= $sysconf['library_name']; ?></span>
            <span><?= __('Powered by '); ?><a target="_blank" rel="noopener" href="https://slims.web.id">SLiMS</a> &middot; <a target="_blank" rel="noopener" href="https://slims.web.id/web/pages/support-us/"><i class="fas fa-heart"></i> <?= __('Keep SLiMS Alive'); ?></a></span>
        </div>
    </div>
</footer>

<?php // Kredit template (terkunci, lihat mlCredit() di classic.php) ?>
<?= mlCredit(); ?>

<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <div id="show-pchat2" style="position: fixed; bottom: 16px; right: 16px; z-index: 50">
        <button title="Chat" class="ml-btn ml-btn-dark"><i class="fas fa-comments mr-2"></i><?= __('Chat'); ?></button>
    </div>
<?php endif; ?>

<?php
// Chat Engine
include LIB . "contents/chat.php"; ?>

<!-- // Load modal -->
<?php include "_modal_topic.php"; ?>
<?php include "_modal_advanced.php"; ?>
<?php include "_modal_social_media.php"; ?>

<!-- // Load highlight -->
<script src="<?= JWB; ?>highlight.js"></script>
<?php if (isset($engine) && $searchableInJsArray = $this->generateKeywords($engine->searchable_fields)) : ?>
<script>
  $('.card-body > *').highlight(<?= $searchableInJsArray ?>);
</script>
<?php endif; ?>

<!-- // load our vue app.js -->
<script src="<?php echo assets(v('js/app.js')); ?>"></script>
<script src="<?php echo assets(v('js/app_jquery.js')); ?>"></script>
<?php include __DIR__ . "/../assets/js/vegas.js.php"; ?>
<script>
  // Modern Library: tombol geser rel koleksi
  $(document).on('click', '.ml-rail-btn', function () {
    var rail = $(this).closest('.ml-rail').find('.collection').get(0);
    if (!rail) return;
    var step = Math.max(rail.clientWidth * 0.8, 200);
    rail.scrollBy({left: $(this).hasClass('ml-rail-prev') ? -step : step, behavior: 'smooth'});
  });

  // Modern Library: jangkar di beranda (mis. index.php#ml-new).
  // Isi koleksi dimuat belakangan oleh Vue, jadi gulir ulang setelah konten siap.
  (function () {
    function goToHash() {
      if (!location.hash || location.hash.length < 2) return;
      var el = document.getElementById(decodeURIComponent(location.hash.slice(1)));
      if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
    }
    $(window).on('load', function () { setTimeout(goToHash, 600); setTimeout(goToHash, 1800); });
    $(window).on('hashchange', goToHash);
  })();
</script>
<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <script>
        $('#show-pchat').click(() => {
            $('.s-chat').hide()
            $('#show-pchat2').show()
        })
        $('#show-pchat2').click(() => {
            $('.s-chat').show(300, () => {
                $('#show-pchat2').hide()
            })
        })
    </script>
<?php endif; ?>
</body>
</html>
