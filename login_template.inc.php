<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-29 22:16
 * @File name           : login_template.inc.php
 */

if (isset($_GET['p']) && $_GET['p'] === 'visitor') {
  // Modern Library: buku tamu tampil dengan header, menu, dan footer yang sama seperti beranda
  $imagesDisk = \SLiMS\Filesystems\Storage::images();
  include "classic.php";
  include "parts/header.php";
  include "parts/_navbar.php";
  echo $main_content;
  include "parts/footer.php";
} else {
  include "index_template.inc.php";
}