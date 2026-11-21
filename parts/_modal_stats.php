<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — popup statistik pengunjung & peminjaman.
 * Data diisi lewat AJAX ke index.php?ml_stats=1 saat popup dibuka
 * (lihat parts/_stats_data.php), bukan dihitung di setiap muat halaman.
 */
?>
<div class="modal fade" id="statsModal" tabindex="-1" role="dialog" aria-labelledby="statsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ml-stats-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="statsModalLabel">
                    <i class="fas fa-chart-line mr-2"></i><?= mlT('Statistik Pengunjung & Peminjaman', 'Visitor & Loan Statistics') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="mlStatsLoading" class="ml-stats-loading">
                    <i class="fas fa-circle-notch fa-spin"></i> <?= mlT('Memuat data…', 'Loading data…') ?>
                </div>
                <div id="mlStatsError" class="ml-stats-error" style="display:none">
                    <?= mlT('Gagal memuat statistik. Coba lagi beberapa saat.', 'Failed to load statistics. Please try again.') ?>
                </div>
                <div id="mlStatsBody" style="display:none">
                    <h6 class="ml-stats-section-title"><i class="fas fa-walking mr-2"></i><?= mlT('Pengunjung Perpustakaan', 'Library Visitors') ?></h6>
                    <div class="ml-stats-grid">
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="visitor.today">0</strong>
                            <span><?= mlT('Hari ini', 'Today') ?></span>
                        </div>
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="visitor.week">0</strong>
                            <span><?= mlT('7 hari terakhir', 'Last 7 days') ?></span>
                        </div>
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="visitor.month">0</strong>
                            <span><?= mlT('Bulan ini', 'This month') ?></span>
                        </div>
                        <div class="ml-stats-tile ml-stats-tile-accent">
                            <strong data-ml-stat="visitor.total">0</strong>
                            <span><?= mlT('Total sepanjang waktu', 'All-time total') ?></span>
                        </div>
                    </div>

                    <h6 class="ml-stats-section-title mt-4"><i class="fas fa-book-reader mr-2"></i><?= mlT('Peminjaman Koleksi', 'Collection Loans') ?></h6>
                    <div class="ml-stats-grid">
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="loan.today">0</strong>
                            <span><?= mlT('Hari ini', 'Today') ?></span>
                        </div>
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="loan.week">0</strong>
                            <span><?= mlT('7 hari terakhir', 'Last 7 days') ?></span>
                        </div>
                        <div class="ml-stats-tile">
                            <strong data-ml-stat="loan.month">0</strong>
                            <span><?= mlT('Bulan ini', 'This month') ?></span>
                        </div>
                        <div class="ml-stats-tile ml-stats-tile-accent">
                            <strong data-ml-stat="loan.total">0</strong>
                            <span><?= mlT('Total sepanjang waktu', 'All-time total') ?></span>
                        </div>
                    </div>

                    <div class="ml-stats-active">
                        <i class="fas fa-book mr-2"></i>
                        <span><strong data-ml-stat="loan.active">0</strong> <?= mlT('eksemplar sedang dipinjam saat ini', 'copies currently on loan') ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-muted text-sm">
                <div><?= mlT('Data diambil langsung dari database perpustakaan.', 'Data is pulled directly from the library database.') ?></div>
            </div>
        </div>
    </div>
</div>
<script>
(function ($) {
    var $modal = $('#statsModal');
    if (!$modal.length) return;
    var loaded = false;

    function setNumber(root, path, value) {
        var n = Number(value) || 0;
        root.find('[data-ml-stat="' + path + '"]').text(n.toLocaleString('id-ID'));
    }

    $modal.on('shown.bs.modal', function () {
        if (loaded) return;
        var $loading = $('#mlStatsLoading'), $error = $('#mlStatsError'), $body = $('#mlStatsBody');
        $loading.show(); $error.hide(); $body.hide();

        $.getJSON('index.php?ml_stats=1')
            .done(function (data) {
                if (!data || !data.visitor || !data.loan) {
                    $loading.hide(); $error.show();
                    return;
                }
                ['today', 'week', 'month', 'total'].forEach(function (k) {
                    setNumber($body, 'visitor.' + k, data.visitor[k]);
                    setNumber($body, 'loan.' + k, data.loan[k]);
                });
                setNumber($body, 'loan.active', data.loan.active);
                $loading.hide(); $body.show();
                loaded = true;
            })
            .fail(function () {
                $loading.hide(); $error.show();
            });
    });
})(jQuery);
</script>
