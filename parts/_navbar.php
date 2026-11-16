<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — header & navigasi utama
 * Baris 1 : logo, kotak pencarian (pil), akun, bookmark, keranjang, bahasa
 * Baris 2 : tombol "Semua Kategori" + menu utama
 */

$main_menus = [
  'home' => [
    'text' => __('Home'),
    'url' => 'index.php'
  ],
  'libinfo' => [
    'text' => __('Information'),
    'url' => 'index.php?p=libinfo'
  ],
  'news' => [
    'text' => __('News'),
    'url' => 'index.php?p=news'
  ],
  'help' => [
    'text' => __('Help'),
    'url' => 'index.php?p=help'
  ],
  'librarian' => [
    'text' => __('Librarian'),
    'url' => 'index.php?p=librarian'
  ]
];

// ---- bahasa
$langstr = '';
$current_lang = ['name' => '', 'code' => 'id'];
$select_lang = $_COOKIE['select_lang'] ?? $sysconf['default_lang'];
foreach ($available_languages ?? [] as $lang_index) {
  $lang_code = $lang_index[0];
  $lang_name = $lang_index[1];
  $code_arr = explode('_', $lang_code);
  $code_flag = strtolower($code_arr[1] ?? $code_arr[0]);
  if ($lang_code == $select_lang) {
    $current_lang = ['name' => $lang_name, 'code' => $code_flag];
  }
  $langstr .= '<a class="dropdown-item" href="index.php?select_lang=' . $lang_code . '">'
    . '<span class="flag-icon flag-icon-' . $code_flag . ' mr-2" style="border-radius:2px;"></span> ' . $lang_name . '</a>';
}

$menu_member_active = isset($_GET['p']) && $_GET['p'] === 'member';
$count_basket = $is_login ? count($_SESSION['m_mark_biblio'] ?? []) : 0;
?>
<header class="ml-header">
    <!-- ===== Baris 1 ===== -->
    <div class="ml-topbar">
        <div class="container ml-topbar-inner">
            <a class="ml-brand" href="index.php" title="<?= htmlspecialchars($sysconf['library_name']) ?>">
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
                    <?php if ($sysconf['template']['classic_library_subname']) : ?>
                        <small><?= $sysconf['library_subname']; ?></small>
                    <?php else: ?>
                        <small><?= mlT('Katalog Online', 'Online Catalog') ?></small>
                    <?php endif; ?>
                </span>
            </a>

            <div class="ml-search-slot">
                <?php include '_search-form.php'; ?>
            </div>

            <div class="ml-actions">
                <?php if ($is_login) : ?>
                    <div class="dropdown">
                        <a href="#" class="ml-action <?= $menu_member_active ? 'active' : '' ?>" id="mlAccountMenu"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="ml-avatar" src="<?= $member_image_path; ?>" alt="avatar">
                            <span><?= htmlspecialchars(explode(' ', $_SESSION['m_name'])[0]); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right ml-dropdown" aria-labelledby="mlAccountMenu">
                            <a class="dropdown-item" href="index.php?p=member"><i class="fas fa-user-circle mr-2"></i> <?= __('Profile'); ?></a>
                            <a class="dropdown-item" href="index.php?p=member&sec=bookmark"><i class="fas fa-bookmark mr-2"></i> <?= __('Bookmark'); ?></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="index.php?p=member&logout=1"><i class="fas fa-sign-out-alt mr-2"></i> <?= __('Logout'); ?></a>
                        </div>
                    </div>
                    <a href="index.php?p=member&sec=bookmark" class="ml-action d-none d-md-flex">
                        <i class="far fa-heart"></i>
                        <span><?= __('Bookmark') ?></span>
                    </a>
                    <a href="index.php?p=member&sec=title_basket" class="ml-action">
                        <i class="fas fa-shopping-basket"></i>
                        <sup id="count-basket" class="ml-badge"><?= $count_basket; ?></sup>
                        <span><?= mlT('Keranjang', 'Basket') ?></span>
                    </a>
                <?php else : ?>
                    <a href="index.php?p=member" class="ml-action <?= $menu_member_active ? 'active' : '' ?>">
                        <i class="far fa-user"></i>
                        <span><?= __('Member Area') ?></span>
                    </a>
                    <a href="index.php?p=member" class="ml-action d-none d-md-flex">
                        <i class="far fa-heart"></i>
                        <span><?= __('Bookmark') ?></span>
                    </a>
                <?php endif; ?>

                <div class="dropdown">
                    <a href="#" class="ml-action" id="mlLangMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="flag-icon flag-icon-<?= $current_lang['code'] ?> ml-flag"></span>
                        <span><?= __('Language') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right ml-dropdown" aria-labelledby="mlLangMenu">
                        <h6 class="dropdown-header"><?= __('Select Language'); ?></h6>
                        <?= $langstr; ?>
                    </div>
                </div>

                <button class="ml-burger d-lg-none" type="button" data-toggle="collapse" data-target="#mlMainNav"
                        aria-controls="mlMainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== Baris 2 ===== -->
    <nav class="ml-navrow">
        <div class="container">
            <div class="collapse ml-navrow-inner" id="mlMainNav">
                <button type="button" class="ml-cat-btn" data-toggle="modal" data-target="#exampleModal">
                    <i class="fas fa-th-large"></i> <?= mlT('Semua Kategori', 'All Categories') ?>
                </button>
                <ul class="ml-menu">
                    <?php foreach ($main_menus as $key => $main_menu) :
                        $active = isset($_GET['p']) ? ($key === $_GET['p']) : ($key === 'home' && !isset($_GET['search']));
                        ?>
                        <li><a class="<?= $active ? 'active' : '' ?>" href="<?= $main_menu['url'] ?>"><?= $main_menu['text'] ?></a></li>
                    <?php endforeach; ?>
                    <li><a class="<?= $menu_member_active ? 'active' : '' ?>" href="index.php?p=member"><?= __('Member Area') ?></a></li>
                </ul>
                <a class="ml-nav-extra" href="javascript:void(0)" data-toggle="modal" data-target="#adv-modal">
                    <i class="fas fa-sliders-h"></i> <?= __('Advanced Search') ?>
                </a>
            </div>
        </div>
    </nav>
</header>
