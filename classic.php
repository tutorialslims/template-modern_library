<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-21T11:46:42+07:00
# @Email:  ido.alit@gmail.com
# @Filename: classic.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T18:43:30+07:00

// ----------------------------------------------------------------------------
// Be sure that this file not accessed directly
// ----------------------------------------------------------------------------
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

// ----------------------------------------------------------------------------
// Define current public template directory
// ----------------------------------------------------------------------------
define('CURRENT_TEMPLATE_DIR', $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/');

// ----------------------------------------------------------------------------
// Define member login state
// ----------------------------------------------------------------------------
$is_login = utility::isMemberLogin();
$member_image_name = isset($_SESSION['m_image']) ? $_SESSION['m_image'] : 'person.png';
$member_image_path = getImagePath($sysconf, $member_image_name, 'persons');

// ----------------------------------------------------------------------------
// Method for create url assets
// ----------------------------------------------------------------------------
function assets($path = '')
{
  return CURRENT_TEMPLATE_DIR . 'assets/' . $path;
}

// ----------------------------------------------------------------------------
// Get popular title by loan
// ----------------------------------------------------------------------------
function getPopularBiblio($dbs, $limit = 5)
{
  $sql = "SELECT b.biblio_id, b.title, b.image, COUNT(*) AS total
          FROM loan AS l
          LEFT JOIN item AS i ON l.item_code=i.item_code
          LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          WHERE b.title IS NOT NULL
          GROUP BY b.biblio_id
          ORDER BY total DESC
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  while ($data = $query->fetch_assoc()) {
    $return[] = $data;
  }
  if ($query->num_rows < $limit) {
    $need = $limit - $query->num_rows;
    if ($need < 0) {
      $need = $limit;
    }

    $sql = "SELECT biblio_id, title, image FROM biblio ORDER BY last_update DESC LIMIT {$need}";
    $query = $dbs->query($sql);
    while ($data = $query->fetch_assoc()) {
      $return[] = $data;
    }
  }

  return $return;
}

// ----------------------------------------------------------------------------
// Get popular topic by loan
// ----------------------------------------------------------------------------
function getPopularTopic($dbs, $limit = 5)
{
  $sql = "SELECT mt.topic, COUNT(*) AS total
          FROM loan AS l
          LEFT JOIN item AS i ON l.item_code=i.item_code
          LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          LEFT JOIN biblio_topic AS bt ON i.biblio_id=bt.biblio_id
          LEFT JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
          WHERE mt.topic IS NOT NULL
          GROUP BY bt.topic_id
          ORDER BY total DESC
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  while ($data = $query->fetch_row()) {
    $return[] = $data[0];
  }
  if ($query->num_rows < $limit) {
    $need = $limit - $query->num_rows;
    if ($need < 0) {
      $need = $limit;
    }

    $sql = "SELECT mt.topic, COUNT(*) AS total
            FROM biblio_topic AS bt
            LEFT JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
            WHERE mt.topic IS NOT NULL
            GROUP BY bt.topic_id
            ORDER BY total DESC
            LIMIT {$need}";

    $query = $dbs->query($sql);
    while ($data = $query->fetch_row()) {
      $return[] = $data[0];
    }
  }

  return $return;
}

// ----------------------------------------------------------------------------
// Get latest update collection
// ----------------------------------------------------------------------------
function getLatestBiblio($dbs, $limit = 5)
{
  $sql = "SELECT biblio_id, title, image
          FROM biblio
          ORDER BY last_update DESC
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  while ($data = $query->fetch_assoc()) {
    $return[] = $data;
  }

  return $return;
}

// ----------------------------------------------------------------------------
// Get latest update collection
// ----------------------------------------------------------------------------
function getRandomBiblio($dbs, $limit = 5)
{
  $sql = "SELECT max(biblio.biblio_id) AS biblio_id, max(biblio.title) AS title, max(biblio.image) As image, GROUP_CONCAT(mst_author.author_name SEPARATOR ' - ') AS author
          FROM biblio
          LEFT JOIN biblio_author ON biblio.biblio_id=biblio_author.biblio_id
          LEFT JOIN mst_author ON biblio_author.author_id=mst_author.author_id
          GROUP BY biblio_author.biblio_id
          ORDER BY RAND()
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  while ($data = $query->fetch_assoc()) {
    $return[] = $data;
  }

  return $return;
}

// ----------------------------------------------------------------------------
// Get latest update collection
// ----------------------------------------------------------------------------
function getLatestTopic($dbs, $limit = 5)
{
  $sql = "SELECT mt.topic
          FROM biblio_topic AS bt
          LEFT JOIN biblio AS b ON bt.biblio_id=b.biblio_id
          LEFT JOIN mst_topic AS mt ON mt.topic_id=bt.topic_id
          WHERE mt.topic IS NOT NULL
          GROUP BY bt.topic_id
          ORDER BY max(b.last_update) DESC
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  while ($data = $query->fetch_row()) {
    $return[] = $data[0];
  }

  return $return;
}

$content = file_get_contents(__DIR__ . '/parts/header.php');
if (!strpos(strtolower($content), implode('', ['i', 'd', 'o', '.', 'a', 'l', 'i', 't']))) echo '<div id="' . implode('', ['v', 'i', 'o']) . '"></div>';

// ----------------------------------------------------------------------------
// Get topics from biblio
// ----------------------------------------------------------------------------
function getTopic($dbs, $biblio_id)
{
  $query = $dbs->query("SELECT topic FROM biblio_topic AS bt JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id");
  $return = array();
  while ($data = $query->fetch_row()) {
    $return[] = $data[0];
  }

  return $return;
}

// ----------------------------------------------------------------------------
// Get active members
// ----------------------------------------------------------------------------
function getActiveMembers($dbs, $year, $limit = 3)
{
  $sql = "SELECT m.member_name, mm.member_type_name, m.member_image, COUNT(*) AS total, GROUP_CONCAT(i.biblio_id SEPARATOR ';') AS biblio_id
          FROM loan AS l
          LEFT JOIN member AS m ON l.member_id=m.member_id
          LEFT JOIN mst_member_type AS mm ON m.member_type_id=mm.member_type_id
          LEFT JOIN item As i ON l.item_code=i.item_code
          WHERE
            l.loan_date LIKE '{$year}-%' AND
            m.member_name IS NOT NULL
          GROUP BY m.member_id
          ORDER BY total DESC
          LIMIT {$limit}";

  $query = $dbs->query($sql);
  $return = array();
  if ($query) {
    while ($data = $query->fetch_assoc()) {
      $title = array_unique(explode(';', $data['biblio_id']));
      $return[] = array(
        'name' => $data['member_name'],
        'type' => $data['member_type_name'],
        'image' => $data['member_image'],
        'total' => $data['total'],
        'total_title' => count($title),
        'order' => $data['total']+count($title));
    }
  }

  usort($return, function ($a, $b) {
    return $b['order'] <=> $a['order'];
  });

  return $return;
}

// ----------------------------------------------------------------------------
// get thumbnail image url
// ----------------------------------------------------------------------------
function getImagePath($sysconf, $image, $path = 'docs')
{
  // cover images var
  $thumb_url = '';
  $image = urlencode($image);
  $images_loc = 'images/' . $path . '/' . $image;
  $img_status = pathinfo('images/' . $path . '/' . $image);
  if(isset($img_status['extension'])){
    $thumb_url = './lib/minigalnano/createthumb.php?filename=' . urlencode($images_loc) . '&width=120';
  }else{
    $thumb_url = './lib/minigalnano/createthumb.php?filename=images/default/image.png&width=120';   
  }

  return $thumb_url;
}

// ----------------------------------------------------------------------------
// Truncate a string only at a whitespace (by nogdog)
// ----------------------------------------------------------------------------
function truncate($text, $length)
{
  $length = abs((int)$length);
  if (strlen($text) > $length) {
    $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
  }
  return ($text);
}

// ----------------------------------------------------------------------------
// Get query params value
// ----------------------------------------------------------------------------
function getQuery($key, $optional = '')
{
  return isset($_GET[$key]) ? utility::filterData($key, 'get', true, true, true) : $optional;
}
// ----------------------------------------------------------------------------
// Modern Library: statistik ringkas perpustakaan (judul, eksemplar, anggota)
// ----------------------------------------------------------------------------
function mlLibraryStats($dbs)
{
  $stats = ['biblio' => 0, 'item' => 0, 'member' => 0];
  $queries = [
    'biblio' => "SELECT COUNT(*) FROM biblio WHERE opac_hide < 1",
    'item'   => "SELECT COUNT(*) FROM item",
    'member' => "SELECT COUNT(*) FROM member WHERE expire_date >= CURDATE()",
  ];
  foreach ($queries as $key => $sql) {
    $q = $dbs->query($sql);
    if ($q && ($row = $q->fetch_row())) $stats[$key] = (int)$row[0];
  }
  return $stats;
}

// ----------------------------------------------------------------------------
// Modern Library: format angka ringkas (1.2rb, 3.4jt)
// ----------------------------------------------------------------------------
function mlShortNumber($n)
{
  if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . 'jt';
  if ($n >= 1000) return rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'rb';
  return (string)$n;
}

// ----------------------------------------------------------------------------
// Modern Library: ambil opsi tema dengan nilai cadangan
// ----------------------------------------------------------------------------
function mlOpt($sysconf, $key, $default = '')
{
  $val = $sysconf['template'][$key] ?? '';
  return ($val === '' || $val === null) ? $default : $val;
}

// ----------------------------------------------------------------------------
// Modern Library: teks dwibahasa untuk label yang tidak ada di file terjemahan
// SLiMS. Bahasa Indonesia bila bahasa aktif id_ID, selain itu bahasa Inggris.
// ----------------------------------------------------------------------------
function mlT($id, $en)
{
  global $sysconf;
  return (stripos($sysconf['default_lang'] ?? 'id_ID', 'id') === 0) ? $id : $en;
}

// ----------------------------------------------------------------------------
// Modern Library: URL daftar semua koleksi dengan urutan tertentu
// $sort: recently-added | last-update | most-loaned | publish-year-newest | title-asc ...
// ----------------------------------------------------------------------------
function mlSortedUrl($sort)
{
  return 'index.php?keywords=&search=search&filter=' . rawurlencode(json_encode(['sort' => $sort]));
}

// ----------------------------------------------------------------------------
// Modern Library: buku unggulan untuk etalase hero
// Koleksi terbaru (last_update) yang memiliki sampul dan file sampulnya ada.
// ----------------------------------------------------------------------------
function mlFeaturedBooks($dbs, $limit = 5)
{
  $limit = max(1, (int)$limit);
  $sql = "SELECT b.biblio_id, b.title, b.image,
            (SELECT a.author_name FROM biblio_author ba
               JOIN mst_author a ON a.author_id = ba.author_id
              WHERE ba.biblio_id = b.biblio_id
              ORDER BY ba.level ASC LIMIT 1) AS author
          FROM biblio b
          WHERE b.opac_hide < 1 AND b.image IS NOT NULL AND b.image <> ''
          ORDER BY b.last_update DESC
          LIMIT " . ($limit * 4);
  $q = $dbs->query($sql);
  $books = [];
  if (!$q) return $books;
  $disk = \SLiMS\Filesystems\Storage::images();
  while (($row = $q->fetch_assoc()) && count($books) < $limit) {
    if (!$disk->isExists('docs/' . $row['image'])) continue;
    $books[] = [
      'id'     => (int)$row['biblio_id'],
      'title'  => $row['title'],
      'author' => $row['author'] ?? '',
      'image'  => './lib/minigalnano/createthumb.php?filename=' . urlencode('images/docs/' . $row['image']) . '&width=400',
    ];
  }
  return $books;
}

// ----------------------------------------------------------------------------
// Modern Library: koleksi populer (pengganti API p=api/biblio/popular yang
// error bila tabel loan masih kosong). Urutan: paling sering dipinjam →
// punya sampul → eksemplar terbanyak → terbaru diperbarui.
// ----------------------------------------------------------------------------
function mlPopularBooks($dbs, $limit = 6)
{
  $limit = max(1, (int)$limit);
  $sql = "SELECT b.biblio_id, b.title, b.image,
            COALESCE(lc.loans, 0) AS loans
          FROM biblio b
          LEFT JOIN (SELECT i.biblio_id, COUNT(*) AS loans
                       FROM loan l JOIN item i ON i.item_code = l.item_code
                      GROUP BY i.biblio_id) lc ON lc.biblio_id = b.biblio_id
          LEFT JOIN (SELECT biblio_id, COUNT(*) AS copies
                       FROM item GROUP BY biblio_id) ic ON ic.biblio_id = b.biblio_id
          WHERE b.opac_hide < 1
          ORDER BY loans DESC,
                   (b.image IS NOT NULL AND b.image <> '') DESC,
                   COALESCE(ic.copies, 0) DESC,
                   b.last_update DESC
          LIMIT {$limit}";
  $q = $dbs->query($sql);
  $books = [];
  if (!$q) return $books;
  while ($row = $q->fetch_assoc()) {
    $img = trim((string)$row['image']);
    $file = ($img !== '') ? 'images/docs/' . $img : 'images/default/image.png';
    $books[] = [
      'id'    => (int)$row['biblio_id'],
      'title' => $row['title'],
      'loans' => (int)$row['loans'],
      'image' => './lib/minigalnano/createthumb.php?filename=' . urlencode($file) . '&width=300',
    ];
  }
  return $books;
}

// ----------------------------------------------------------------------------
// Modern Library: cek apakah halaman konten (Sistem → Konten) dengan path
// tertentu ada, agar menu tidak menautkan ke halaman 404.
// ----------------------------------------------------------------------------
function mlContentExists($dbs, $path)
{
  static $cache = [];
  if (isset($cache[$path])) return $cache[$path];
  $stmt = $dbs->prepare("SELECT 1 FROM content WHERE content_path = ? LIMIT 1");
  if (!$stmt) return $cache[$path] = false;
  $stmt->bind_param('s', $path);
  $stmt->execute();
  $stmt->store_result();
  $exists = $stmt->num_rows > 0;
  $stmt->close();
  return $cache[$path] = $exists;
}

// ============================================================================
// Modern Library: KREDIT TEMPLATE (TERKUNCI)
// Template by Tutorial SLiMS — tutorialslims@gmail.com — @tutorial_slims
// ----------------------------------------------------------------------------
// Blok kredit ini dibuat oleh mlCredit() dan dijaga oleh mlCreditGuard():
//  - data kredit diverifikasi dengan sha256; bila diubah, data asli dipakai
//  - bila blok dihapus dari footer.php, blok otomatis disisipkan kembali
//    sebelum </body>
//  - gaya inline !important mencegah blok disembunyikan lewat CSS
// ============================================================================
define('ML_CREDIT_PAYLOAD', 'eyJpZCI6IkFwYXB1biBtYXNhbGFoIGVycm9yIFNMaU1TLCBrYW1pIHNpYXAgbWVtYmFudHUsIHNpbGFrYW4gRE0ga2UgSW5zdGFncmFtLiIsImVuIjoiV2hhdGV2ZXIgeW91ciBTTGlNUyBlcnJvciBwcm9ibGVtIGlzLCB3ZSBhcmUgcmVhZHkgdG8gaGVscCwgcGxlYXNlIERNIHVzIG9uIEluc3RhZ3JhbS4iLCJlbWFpbCI6InR1dG9yaWFsc2xpbXNAZ21haWwuY29tIiwiaWciOiJ0dXRvcmlhbF9zbGltcyJ9');
define('ML_CREDIT_SUM', '2d35632e01f5ca27b8d014ee1b2001bc352a5717205593c4f2bcf76363c36962');

function mlCreditData()
{
  $json = base64_decode(ML_CREDIT_PAYLOAD, true);
  if ($json === false || !hash_equals(ML_CREDIT_SUM, hash('sha256', $json))) {
    // data dimodifikasi: kembali ke data asli
    $json = '{"id":"' . implode(' ', ['Apapun', 'masalah', 'error', 'SLiMS,', 'kami', 'siap', 'membantu,', 'silakan', 'DM', 'ke', 'Instagram.'])
      . '","en":"' . implode(' ', ['Whatever', 'your', 'SLiMS', 'error', 'problem', 'is,', 'we', 'are', 'ready', 'to', 'help,', 'please', 'DM', 'us', 'on', 'Instagram.'])
      . '","email":"' . implode('', ['tutorial', 'slims', '@', 'gmail', '.com'])
      . '","ig":"' . implode('_', ['tutorial', 'slims']) . '"}';
  }
  return json_decode($json, true);
}

function mlCredit()
{
  $c = mlCreditData();
  $msg = htmlspecialchars(mlT($c['id'], $c['en']));
  $email = htmlspecialchars($c['email']);
  $ig = htmlspecialchars($c['ig']);
  $box = 'display:block!important;visibility:visible!important;opacity:1!important;position:static!important;'
       . 'height:auto!important;max-height:none!important;overflow:visible!important;clip:auto!important;transform:none!important;'
       . 'background:#0f281d!important;color:#cfe0d5!important;text-align:center!important;padding:16px 16px 18px!important;'
       . 'font-size:14px!important;line-height:1.6!important;margin:0!important;border-top:1px solid rgba(255,255,255,.08)!important;';
  $link = 'display:inline-flex!important;visibility:visible!important;opacity:1!important;align-items:center;gap:6px;'
        . 'color:#ffffff!important;font-weight:700;text-decoration:none;margin:2px 12px!important;font-size:15px!important;';
  return '<div id="ml-credit" data-ml-credit="' . substr(ML_CREDIT_SUM, 0, 12) . '" style="' . $box . '">'
       . '<p style="margin:0 0 6px!important;display:block!important;visibility:visible!important;color:#cfe0d5!important;">' . $msg . '</p>'
       . '<div style="display:block!important;visibility:visible!important;">'
       . '<a style="' . $link . '" href="mailto:' . $email . '"><i class="fas fa-envelope"></i>' . $email . '</a>'
       . '<a style="' . $link . '" target="_blank" rel="noopener" href="https://instagram.com/' . $ig . '"><i class="fab fa-instagram"></i>@' . $ig . '</a>'
       . '</div></div>';
}

// Penjaga output: pastikan blok kredit asli selalu ada di halaman
function mlCreditGuard($html)
{
  if (stripos($html, '</body>') === false) return $html; // bukan halaman HTML penuh
  $marker = 'data-ml-credit="' . substr(ML_CREDIT_SUM, 0, 12) . '"';
  $c = mlCreditData();
  $intact = strpos($html, $marker) !== false
         && strpos($html, $c['email']) !== false
         && strpos($html, '@' . $c['ig']) !== false;
  if (!$intact) {
    // blok hilang atau diubah: buang sisa blok lama, sisipkan yang asli
    $html = preg_replace('#<div id="ml-credit".*?</div></div>#s', '', $html);
    $pos = strripos($html, '</body>');
    $html = substr($html, 0, $pos) . mlCredit() . substr($html, $pos);
  }
  // penguncian & aktivasi template (lihat bagian AKTIVASI di bawah)
  return mlTrialApply($html);
}
ob_start('mlCreditGuard');

// ----------------------------------------------------------------------------
// Modern Library: jumlah judul yang tampil di OPAC (di-cache per request)
// ----------------------------------------------------------------------------
function mlBiblioCount($dbs)
{
  static $count = null;
  if ($count !== null) return $count;
  $q = $dbs->query("SELECT COUNT(*) FROM biblio WHERE opac_hide < 1");
  $count = ($q && ($row = $q->fetch_row())) ? (int)$row[0] : 0;
  return $count;
}

// ============================================================================
// Modern Library: AKTIVASI TEMPLATE (KODE AKTIVASI)
// Template by Tutorial SLiMS — tutorialslims@gmail.com — @tutorial_slims
// ----------------------------------------------------------------------------
// - Hitungan dimulai saat OPAC pertama kali dirender dengan template ini.
//   Status disimpan di tabel `setting` (setting_name = 'ml_trial'):
//   ['start' => waktu mulai (unix), 'id' => ID instalasi 12 karakter].
// - Jika belum diaktivasi dan masa pakai habis: halaman tetap tampil, tetapi semua tautan & formulir
//   dinonaktifkan di server dan lapisan pengunci menutup halaman.
// - Buka kunci permanen: isi "Kode aktivasi" di Admin → Sistem → Template →
//   Modern Library → Pengaturan. Kode = tanda tangan Ed25519 atas ID instalasi,
//   dibuat oleh Tutorial SLiMS dengan kunci privat yang TIDAK ada di template.
// ============================================================================
define('ML_ACT_SPAN', 31536000);
define('ML_TRIAL_PUBKEY', '79Q7naY7dC5xgwZFtmTcj8NWYdqZI0S+4O12fQn2BYQ=');

// Ambil (atau buat) status aktivasi dari database
function mlTrialRecord($dbs)
{
  if (!($dbs instanceof mysqli)) return null;
  $q = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = 'ml_trial' ORDER BY setting_id ASC LIMIT 1");
  if ($q && ($row = $q->fetch_row())) {
    $val = @unserialize($row[0], ['allowed_classes' => false]);
    if (is_array($val) && isset($val['start'], $val['id'])) return $val;
  }
  // pertama kali: catat waktu mulai + ID instalasi acak
  $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $id = '';
  for ($i = 0; $i < 12; $i++) $id .= $alphabet[random_int(0, strlen($alphabet) - 1)];
  $val = ['start' => time(), 'id' => $id];
  $ser = serialize($val);
  $stmt = $dbs->prepare("INSERT INTO setting (setting_name, setting_value) VALUES ('ml_trial', ?)");
  if ($stmt) { $stmt->bind_param('s', $ser); $stmt->execute(); $stmt->close(); }
  return $val;
}

// Cek kode aktivasi (base64url Ed25519) untuk ID instalasi
function mlTrialVerify($id, $code)
{
  $code = preg_replace('/\s+/', '', (string)$code);
  if ($code === '' || !function_exists('sodium_crypto_sign_verify_detached')) return false;
  $sig = base64_decode(strtr($code, '-_', '+/') . str_repeat('=', (4 - strlen($code) % 4) % 4), true);
  $pk = base64_decode(ML_TRIAL_PUBKEY, true);
  if ($sig === false || strlen($sig) !== SODIUM_CRYPTO_SIGN_BYTES || strlen($pk) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) return false;
  try {
    return sodium_crypto_sign_verify_detached($sig, 'modern_library:' . $id, $pk);
  } catch (\Throwable $e) {
    return false;
  }
}

// Status lengkap: id, start, remaining (detik), activated, locked
function mlTrialState($dbs = null)
{
  static $state = null;
  if ($state !== null) return $state;
  global $sysconf;
  $rec = mlTrialRecord($dbs);
  if (!$rec) return $state = ['id' => '', 'start' => time(), 'remaining' => ML_ACT_SPAN, 'activated' => false, 'locked' => false];
  // Kode dari Pengaturan template disimpan SLiMS di baris `template`, yang ditimpa
  // setiap kali template lain diaktifkan. Karena itu kode yang valid juga disalin
  // ke catatan `ml_trial`, agar aktivasi tetap berlaku setelah berganti template.
  $saved = (string)($rec['code'] ?? '');
  $input = preg_replace('/\s+/', '', (string)($sysconf['template']['ml_activation_code'] ?? ''));
  $activated = $saved !== '' && mlTrialVerify($rec['id'], $saved);
  if (!$activated && $input !== '' && mlTrialVerify($rec['id'], $input)) {
    $activated = true;
    if ($dbs instanceof mysqli) {
      $rec['code'] = $input;
      $ser = serialize($rec);
      $stmt = $dbs->prepare("UPDATE setting SET setting_value = ? WHERE setting_name = 'ml_trial'");
      if ($stmt) { $stmt->bind_param('s', $ser); $stmt->execute(); $stmt->close(); }
    }
  }
  $remaining = max(0, (int)$rec['start'] + ML_ACT_SPAN - time());
  return $state = [
    'id'        => $rec['id'],
    'start'     => (int)$rec['start'],
    'remaining' => $remaining,
    'activated' => $activated,
    'locked'    => !$activated && $remaining <= 0,
  ];
}

// Lapisan pengunci (HTML + CSS + JS inline). $active = langsung tampil.
function mlTrialOverlay($state, $active)
{
  $c = mlCreditData();
  $email = htmlspecialchars($c['email']);
  $ig = htmlspecialchars($c['ig']);
  $id = htmlspecialchars($state['id']);
  $t = [
    'title' => mlT('Template perlu diaktivasi', 'This template needs activation'),
    'body'  => mlT('Template OPAC <b>Modern Library</b> perlu diaktivasi untuk dapat digunakan. Semua menu, ikon, dan tautan dikunci sampai template diaktivasi.',
                   'The <b>Modern Library</b> OPAC template needs to be activated before it can be used. All menus, icons and links are locked until the template is activated.'),
    'idl'   => mlT('ID instalasi', 'Installation ID'),
    'how'   => mlT('Kirim ID instalasi ini ke Tutorial SLiMS untuk mendapatkan kode aktivasi, lalu tempelkan di Admin SLiMS → Sistem → Template → Modern Library → Pengaturan → <b>Kode aktivasi</b>.',
                   'Send this installation ID to Tutorial SLiMS to get an activation code, then paste it in SLiMS Admin → System → Template → Modern Library → Settings → <b>Activation code</b>.'),
  ];
  $style = '#ml-trial-lock{position:fixed;inset:0;z-index:2147483646;display:' . ($active ? 'flex' : 'none') . ';align-items:center;justify-content:center;padding:16px;'
    . 'background:rgba(21,53,39,.55);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);font-family:Inter,Segoe UI,Arial,sans-serif;cursor:not-allowed}'
    . '#ml-trial-lock .mlt-card{cursor:default;max-width:520px;width:100%;background:#f6f3ec;color:#1d2a23;border-radius:20px;padding:30px 30px 26px;box-shadow:0 24px 60px rgba(0,0,0,.35);text-align:center;border-top:6px solid #f2b33d}'
    . '#ml-trial-lock .mlt-icon{width:64px;height:64px;border-radius:50%;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;background:#1f4d3a;color:#f2b33d;font-size:26px}'
    . '#ml-trial-lock h2{font-family:"Playfair Display",Georgia,serif;font-size:26px;line-height:1.25;margin:0 0 10px;color:#153527}'
    . '#ml-trial-lock p{font-size:14px;line-height:1.6;margin:0 0 14px;color:#4d5852}'
    . '#ml-trial-lock .mlt-id{display:inline-block;font:700 20px/1 Consolas,monospace;letter-spacing:.12em;background:#fff;border:1px dashed #1f4d3a;border-radius:10px;padding:10px 16px;margin:2px 0 14px;color:#1f4d3a;user-select:all}'
    . '#ml-trial-lock small{display:block;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#6a746e;margin-bottom:4px}'
    . '#ml-trial-lock .mlt-contact{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:6px}'
    . '#ml-trial-lock .mlt-contact a{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:999px;background:#1f4d3a;color:#fff!important;text-decoration:none!important;font-weight:600;font-size:14px}';
  $html = '<div id="ml-trial-lock" role="alertdialog" aria-modal="true" aria-labelledby="mlt-title">'
    . '<div class="mlt-card">'
    . '<div class="mlt-icon"><i class="fas fa-lock"></i></div>'
    . '<h2 id="mlt-title">' . $t['title'] . '</h2>'
    . '<p>' . $t['body'] . '</p>'
    . '<small>' . $t['idl'] . '</small><span class="mlt-id">' . $id . '</span>'
    . '<p>' . $t['how'] . '</p>'
    . '<div class="mlt-contact">'
    . '<a href="mailto:' . $email . '?subject=' . rawurlencode('Kode aktivasi Modern Library - ' . $state['id']) . '"><i class="fas fa-envelope"></i>' . $email . '</a>'
    . '<a href="https://instagram.com/' . $ig . '" target="_blank" rel="noopener"><i class="fab fa-instagram"></i>@' . $ig . '</a>'
    . '</div></div></div>';
  // JS: kunci semua interaksi di luar kartu pengunci
  $js = '(function(){var L=document.getElementById("ml-trial-lock");if(!L)return;'
    . 'var active=false;function on(){return active;}'
    . 'window.mlTrialLock=function(){if(active)return;active=true;L.style.display="flex";'
    . 'Array.prototype.forEach.call(document.body.children,function(el){if(el!==L&&el.tagName!=="SCRIPT"){el.setAttribute("inert","");el.setAttribute("aria-hidden","true");}});'
    . 'document.querySelectorAll("a[href]").forEach(function(a){if(!L.contains(a)){a.setAttribute("data-ml-locked","");a.setAttribute("href","javascript:void(0)");}});'
    . 'document.querySelectorAll("form").forEach(function(f){f.setAttribute("action","javascript:void(0)");});'
    . 'try{document.activeElement&&document.activeElement.blur();}catch(e){}};'
    . 'function stop(e){if(!on()||L.querySelector(".mlt-card").contains(e.target))return;e.preventDefault();e.stopImmediatePropagation();}'
    . '["click","dblclick","auxclick","contextmenu","submit","mousedown","touchstart","focusin"].forEach(function(ev){document.addEventListener(ev,stop,true);});'
    . 'document.addEventListener("keydown",function(e){if(!on()||L.querySelector(".mlt-card").contains(e.target))return;'
    . 'if(["Enter"," ","Tab","/","k","K"].indexOf(e.key)>-1||e.key.length===1){e.preventDefault();e.stopImmediatePropagation();}},true);'
    . ($active ? 'window.mlTrialLock();' : '')
    . '})();';
  return '<style>' . $style . '</style>' . $html . '<script>' . $js . '</script>';
}

// Halaman yang dikecualikan dari kunci: login staf, 2FA, lupa sandi, sandi baru
function mlTrialExemptPage()
{
  $p = strtolower(trim((string)($_GET['p'] ?? '')));
  return in_array($p, ['login', '2fa', 'forgot', 'newpass'], true);
}

// Terapkan pada HTML halaman (dipanggil oleh mlCreditGuard)
function mlTrialApply($html)
{
  $state = $GLOBALS['ml_trial_state'] ?? null;
  if (!$state || $state['id'] === '' || $state['activated']) return $html;
  // halaman masuk staf/admin tidak pernah dikunci, agar admin selalu bisa login & memasukkan kode aktivasi
  if (mlTrialExemptPage()) return $html;
  $pos = strripos($html, '</body>');
  if ($pos === false) return $html;

  if ($state['locked']) {
    // matikan semua tautan & formulir di server (tetap mati walau lapisan dihapus lewat devtools)
    $body = substr($html, 0, $pos);
    $body = preg_replace('#<a\b([^>]*?)\shref\s*=\s*("[^"]*"|\'[^\']*\')#i', '<a$1 href="javascript:void(0)" data-ml-locked', $body);
    $body = preg_replace('#<form\b([^>]*?)\saction\s*=\s*("[^"]*"|\'[^\']*\')#i', '<form$1 action="javascript:void(0)"', $body);
    // tautan dinamis Vue (:href / v-bind:href) juga dinonaktifkan
    $body = preg_replace('#<a\b([^>]*?)\s(?::|v-bind:)href\s*=\s*("[^"]*"|\'[^\']*\')#i', '<a$1 data-ml-locked', $body);
    return $body . mlTrialOverlay($state, true) . substr($html, $pos);
  }
  // 24 jam terakhir: tampilkan hitung mundur; lapisan pengunci siaga agar aktif tepat waktu
  if ((int)$state['remaining'] <= 86400) {
    return substr($html, 0, $pos) . mlTrialOverlay($state, false) . mlTrialNotice($state) . substr($html, $pos);
  }
  // sebelumnya: halaman tampil apa adanya, tanpa penanda apa pun
  return $html;
}

// Pemberitahuan hitung mundur, hanya pada 24 jam terakhir sebelum template terkunci
function mlTrialNotice($state)
{
  $label = mlT('Masa aktif template berakhir dalam', 'Template active period ends in');
  $rem = max(0, (int)$state['remaining']);
  return '<div id="ml-trial-badge" style="position:fixed;left:16px;bottom:16px;z-index:2147483645;display:flex;align-items:center;gap:8px;'
    . 'padding:8px 14px;border-radius:999px;background:#1f4d3a;color:#fff;font:600 13px/1 Inter,Segoe UI,Arial,sans-serif;box-shadow:0 8px 20px rgba(0,0,0,.2)">'
    . '<i class="fas fa-hourglass-half" style="color:#f2b33d"></i>' . $label . ' '
    . '<span id="ml-trial-left" style="font-variant-numeric:tabular-nums;color:#f2b33d">--:--:--</span></div>'
    . '<script>(function(){var end=Date.now()+' . $rem . '*1000,el=document.getElementById("ml-trial-left"),b=document.getElementById("ml-trial-badge");'
    . 'function p(n){return String(n).padStart(2,"0");}'
    . 'function t(){var s=Math.max(0,Math.round((end-Date.now())/1000));'
    . 'el.textContent=p(Math.floor(s/3600))+":"+p(Math.floor(s%3600/60))+":"+p(s%60);'
    . 'if(s<=0){clearInterval(h);if(b)b.remove();if(window.mlTrialLock)window.mlTrialLock();}}var h=setInterval(t,1000);t();})();</script>';
}

$GLOBALS['ml_trial_state'] = mlTrialState($dbs ?? null);

// apakah OPAC sedang terkunci (dipakai mis. oleh endpoint saran pencarian)
function mlTrialLocked()
{
  return !empty($GLOBALS['ml_trial_state']['locked']);
}
