<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — saran koleksi langsung (live suggestion) untuk kotak pencarian.
 * Dipanggil lewat: index.php?ml_suggest=<kata kunci>&field=<kolom>
 * Mengembalikan JSON: [{id,title,authors,year,call_number,image}]
 *
 * Data diambil langsung dari tabel katalog (biblio + pengarang/subjek/penerbit),
 * bukan dari search_biblio, agar tetap lengkap walau indeks belum dibangun ulang.
 */

$ml_q = trim(strip_tags((string)($_GET['ml_suggest'] ?? '')));
$ml_field = (string)($_GET['field'] ?? 'keywords');
$ml_allowed = ['keywords', 'title', 'author', 'subject', 'isbn', 'publisher', 'callnumber'];
if (!in_array($ml_field, $ml_allowed, true)) $ml_field = 'keywords';

// aktivasi: muat helper template (sekali saja) untuk cek status kunci
include_once __DIR__ . '/../classic.php';

$ml_out = [];
if (!mlTrialLocked() && mb_strlen($ml_q) >= 2 && mb_strlen($ml_q) <= 100) {
  $like   = '%' . addcslashes($ml_q, '%_\\') . '%';
  $prefix = addcslashes($ml_q, '%_\\') . '%';

  $c_title     = "b.title LIKE ?";
  $c_author    = "EXISTS (SELECT 1 FROM biblio_author ba JOIN mst_author a ON a.author_id = ba.author_id WHERE ba.biblio_id = b.biblio_id AND a.author_name LIKE ?)";
  $c_subject   = "EXISTS (SELECT 1 FROM biblio_topic bt JOIN mst_topic t ON t.topic_id = bt.topic_id WHERE bt.biblio_id = b.biblio_id AND t.topic LIKE ?)";
  $c_isbn      = "REPLACE(b.isbn_issn, '-', '') LIKE ?";
  $c_publisher = "EXISTS (SELECT 1 FROM mst_publisher p WHERE p.publisher_id = b.publisher_id AND p.publisher_name LIKE ?)";
  $c_callnum   = "b.call_number LIKE ?";

  $isbn_like = '%' . addcslashes(str_replace('-', '', $ml_q), '%_\\') . '%';
  switch ($ml_field) {
    case 'title':      $where = $c_title;     $params = [$like]; break;
    case 'author':     $where = $c_author;    $params = [$like]; break;
    case 'subject':    $where = $c_subject;   $params = [$like]; break;
    case 'isbn':       $where = $c_isbn;      $params = [$isbn_like]; break;
    case 'publisher':  $where = $c_publisher; $params = [$like]; break;
    case 'callnumber': $where = $c_callnum;   $params = [$prefix]; break;
    default:
      $where  = "($c_title OR $c_author OR $c_subject OR $c_isbn OR $c_callnum)";
      $params = [$like, $like, $like, $isbn_like, $prefix];
  }

  $sql = "SELECT b.biblio_id, b.title, b.publish_year, b.call_number, b.image,
            (SELECT GROUP_CONCAT(a2.author_name ORDER BY ba2.level SEPARATOR '; ')
               FROM biblio_author ba2 JOIN mst_author a2 ON a2.author_id = ba2.author_id
              WHERE ba2.biblio_id = b.biblio_id) AS authors
          FROM biblio b
          WHERE b.opac_hide < 1 AND $where
          ORDER BY (b.title LIKE ?) DESC, b.title ASC
          LIMIT 6";
  $params[] = $prefix;

  $stmt = $dbs->prepare($sql);
  if ($stmt) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    if ($stmt->execute()) {
      $res = $stmt->get_result();
      while ($row = $res->fetch_assoc()) {
        $img = trim((string)$row['image']);
        $authors = (string)$row['authors'];
        if (mb_strlen($authors) > 70) $authors = mb_substr($authors, 0, 67) . '…';
        $ml_out[] = [
          'id'          => (int)$row['biblio_id'],
          'title'       => html_entity_decode(strip_tags($row['title']), ENT_QUOTES, 'UTF-8'),
          'authors'     => $authors,
          'year'        => (string)$row['publish_year'],
          'call_number' => (string)$row['call_number'],
          'image'       => $img !== '' ? './lib/minigalnano/createthumb.php?filename=' . urlencode('images/docs/' . $img) . '&width=80' : '',
        ];
      }
    }
    $stmt->close();
  }
}

// buang semua output sebelumnya (jika ada) lalu kirim JSON murni
while (ob_get_level() > 0) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode($ml_out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
