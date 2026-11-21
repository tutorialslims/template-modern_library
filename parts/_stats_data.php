<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — statistik pengunjung & peminjaman (JSON) untuk popup di navbar.
 * Dipanggil lewat: index.php?ml_stats=1
 * Mengembalikan JSON: {visitor:{today,week,month,total}, loan:{today,week,month,total,active}}
 */

// masa aktif template: muat helper (sekali saja) untuk cek status kunci
include_once __DIR__ . '/../classic.php';

$ml_stats_out = mlTrialLocked() ? null : mlVisitLoanStats($dbs);

// buang semua output sebelumnya (jika ada) lalu kirim JSON murni
while (ob_get_level() > 0) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode($ml_stats_out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
