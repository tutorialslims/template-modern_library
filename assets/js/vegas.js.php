<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — visual hero
 * 1. Etalase buku (#ml-showcase): sampul koleksi terbaru, berganti otomatis.
 * 2. Cadangan bila belum ada koleksi bersampul: slideshow gambar (vegas)
 *    dari assets/images/slide1.jpg ... slide4.jpg
 */
$ml_slides = [];
foreach (['slide1.jpg', 'slide2.jpg', 'slide3.jpg', 'slide4.jpg'] as $ml_slide) {
    if (file_exists(__DIR__ . '/../images/' . $ml_slide)) {
        $ml_slides[] = ['src' => CURRENT_TEMPLATE_DIR . v('assets/images/' . $ml_slide)];
    }
}
$ml_delay = (int)($sysconf['template']['classic_slide_delay'] ?: 5000);
?>
<script>
(function () {
  var $dots = $('#ml-hero-dots');

  // ---------- 1. etalase buku ----------
  var $show = $('#ml-showcase');
  if ($show.length) {
    var $items = $show.children('.ml-show-item');
    var current = 0, timer = null;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function go(i) {
      current = (i + $items.length) % $items.length;
      $items.removeClass('active').attr({'aria-hidden': 'true', tabindex: '-1'})
        .eq(current).addClass('active').attr({'aria-hidden': 'false', tabindex: '0'});
      $dots.children().removeClass('active').eq(current).addClass('active');
    }
    function play() {
      stop();
      if ($items.length > 1 && !reduce) timer = setInterval(function () { go(current + 1); }, <?= $ml_delay ?>);
    }
    function stop() { if (timer) clearInterval(timer); timer = null; }

    if ($items.length > 1) {
      $items.each(function (i) {
        $('<button type="button" aria-label="Buku ' + (i + 1) + '"></button>')
          .on('click', function () { go(i); play(); })
          .appendTo($dots);
      });
    }
    go(0);
    play();
    $show.on('mouseenter focusin', stop).on('mouseleave focusout', play);
    return;
  }

  // ---------- 2. cadangan: slideshow gambar ----------
  var $hero = $('#ml-hero-media');
  if (!$hero.length) return;
  var slides = <?= json_encode($ml_slides, JSON_UNESCAPED_SLASHES) ?>;
  if (!slides.length) return;
  <?php if ($sysconf['template']['classic_library_disableslide']) : ?>
  $hero.css('background-image', 'url(' + slides[0].src + ')');
  return;
  <?php endif; ?>
  slides.forEach(function (s, i) {
    $('<button type="button" aria-label="Slide ' + (i + 1) + '"></button>')
      .on('click', function () { $hero.vegas('jump', i); })
      .appendTo($dots);
  });
  $hero.vegas({
    delay: <?= $ml_delay ?>,
    timer: false,
    transition: '<?= addslashes($sysconf['template']['classic_slide_transition']); ?>',
    animation: '<?= addslashes($sysconf['template']['classic_slide_animation']); ?>',
    slides: slides,
    walk: function (index) {
      $dots.children().removeClass('active').eq(index).addClass('active');
    }
  });
})();
</script>
