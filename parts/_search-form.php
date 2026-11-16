<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — kotak pencarian bentuk pil di header.
 * Fitur:
 *  - pilih kolom: Semua, Judul, Pengarang, Subjek, ISBN/ISSN, Penerbit, Nomor Panggil Rak
 *  - SARAN KOLEKSI LANGSUNG saat mengetik (judul, pengarang, tahun, nomor panggil),
 *    bisa dipilih dengan ↑ ↓ Enter (endpoint: parts/_suggest.php)
 *  - kata kunci & kolom tetap terisi di halaman hasil
 *  - riwayat pencarian (chip) + hapus riwayat
 *  - tautan ke seluruh koleksi
 * Logika ada di Vue app "#search-wraper" (assets/js/app.js).
 */
$ml_fields = [
  'keywords'   => ['fas fa-th-list',  mlT('Semua', 'All')],
  'title'      => ['fas fa-book',     __('Title')],
  'author'     => ['fas fa-user',     __('Author')],
  'subject'    => ['fas fa-tags',     __('Subject')],
  'isbn'       => ['fas fa-barcode',  __('ISBN/ISSN')],
  'publisher'  => ['fas fa-building', __('Publisher')],
  'callnumber' => ['fas fa-map-signs', mlT('Nomor Panggil Rak', 'Call Number')],
];
$ml_placeholders = [
  'keywords'   => __('Enter keyword to search collection...'),
  'title'      => mlT('Ketik judul buku...', 'Type a book title...'),
  'author'     => mlT('Ketik nama pengarang...', 'Type an author name...'),
  'subject'    => mlT('Ketik subjek/topik...', 'Type a subject or topic...'),
  'isbn'       => mlT('Ketik nomor ISBN/ISSN...', 'Type an ISBN/ISSN number...'),
  'publisher'  => mlT('Ketik nama penerbit...', 'Type a publisher name...'),
  'callnumber' => mlT('Ketik nomor panggil, mis. 155.2...', 'Type a call number, e.g. 155.2...'),
];

// kolom & kata kunci yang sedang dicari (agar tetap terisi di halaman hasil)
$ml_current_field = 'keywords';
$ml_current_value = '';
if (isset($_GET['search'])) {
  foreach (array_keys($ml_fields) as $f) {
    if (isset($_GET[$f]) && is_string($_GET[$f]) && trim($_GET[$f]) !== '') {
      $ml_current_field = $f;
      $ml_current_value = trim(strip_tags($_GET[$f]));
      break;
    }
  }
}
$ml_total_docs = mlBiblioCount($dbs);
$ml_js_str = fn($s) => htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
?>
<div class="ml-search" id="search-wraper" xmlns:v-bind="http://www.w3.org/1999/xhtml"
     data-field="<?= $ml_current_field ?>" data-value="<?= htmlspecialchars($ml_current_value, ENT_QUOTES) ?>">
    <form class="ml-search-form" action="index.php" method="get" @submit.prevent="onEnter" role="search" autocomplete="off">
        <input type="hidden" name="search" value="search">
        <label class="ml-search-field-wrap" title="<?= __('Search by :'); ?>">
            <span class="sr-only"><?= __('Search by :'); ?></span>
            <select class="ml-search-field" v-model="searchBy" @change="$refs.keywords.focus()">
                <?php foreach ($ml_fields as $k => [$icon, $label]) : ?>
                    <option value="<?= $k ?>"<?= $k === $ml_current_field ? ' selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </label>
        <input ref="keywords" value="<?= htmlspecialchars($ml_current_value, ENT_QUOTES) ?>" v-model.trim="keywords"
               @focus="searchOnFocus" @blur="searchOnBlur"
               @keydown.down.prevent="moveActive(1)" @keydown.up.prevent="moveActive(-1)" @keydown.esc="show = false"
               @keydown.enter.prevent="onEnter"
               type="text" id="search-input" :name="searchBy" name="<?= $ml_current_field ?>"
               class="ml-search-input" autocomplete="off" role="combobox" aria-autocomplete="list"
               aria-controls="ml-suggest-list" :aria-expanded="show ? 'true' : 'false'"
               aria-label="<?= __('Search'); ?>"
               placeholder="<?= htmlspecialchars($ml_placeholders[$ml_current_field]) ?>"
               :placeholder="(<?= $ml_js_str($ml_placeholders) ?>)[searchBy]"/>
        <span class="ml-search-spin" v-if="suggestLoading" aria-hidden="true"></span>
        <button type="button" class="ml-search-clear" v-if="keywords !== ''" @click="keywords = ''; $refs.keywords.focus()"
                aria-label="<?= mlT('Hapus kata kunci', 'Clear keyword') ?>"><i class="fas fa-times"></i></button>
        <button type="submit" class="ml-search-btn" aria-label="<?= __('Search'); ?>">
            <i class="fas fa-search"></i>
        </button>
    </form>
    <transition name="slide-fade">
        <div v-if="show" class="ml-search-panel" id="advanced-wraper" v-click-outside="hideSearch">
            <p class="ml-panel-label">
                <i class="fas fa-bullseye mr-1"></i><?= mlT('Cari berdasarkan kolom', 'Search by field'); ?>:
                <i @click="show = false" class="far fa-times-circle float-right cursor-pointer" title="<?= __('Close') ?>"></i>
            </p>
            <div class="d-flex flex-wrap">
                <?php foreach ($ml_fields as $k => [$icon, $label]) : ?>
                    <a v-bind:class="{'is-active': searchBy === '<?= $k ?>'}" @click="setField('<?= $k ?>')" class="ml-chip">
                        <i class="<?= $icon ?> mr-2"></i><?= $label ?>
                    </a>
                <?php endforeach; ?>
                <a class="ml-chip ml-chip-adv" data-toggle="modal" data-target="#adv-modal"><i class="fas fa-magic mr-2"></i><?= __('Advanced Search'); ?></a>
            </div>

            <!-- ===== saran koleksi langsung ===== -->
            <div class="ml-suggest" v-if="keywords.length >= 2">
                <p class="ml-panel-label mt-3">
                    <i class="fas fa-bolt ml-bolt mr-1"></i><?= mlT('Koleksi yang cocok', 'Matching collections') ?>
                    <span v-if="suggestQuery" class="ml-suggest-q">“{{ suggestQuery }}”</span>
                </p>
                <div v-if="suggestLoading && suggestions.length === 0" class="ml-suggest-empty">
                    <span class="ml-search-spin"></span> <?= mlT('Mencari koleksi…', 'Searching…') ?>
                </div>
                <div v-else-if="!suggestLoading && suggestQuery && suggestions.length === 0" class="ml-suggest-empty">
                    <i class="far fa-frown mr-1"></i><?= mlT('Tidak ada koleksi yang cocok. Coba kata kunci atau kolom lain.', 'No matching collections. Try another keyword or field.') ?>
                </div>
                <ul v-else class="ml-suggest-list" id="ml-suggest-list" role="listbox">
                    <li v-for="(item, i) in suggestions" :key="item.id" role="option" :aria-selected="i === activeIndex ? 'true' : 'false'">
                        <a :href="'index.php?p=show_detail&id=' + item.id" class="ml-suggest-item"
                           :class="{'is-active': i === activeIndex}" @mouseenter="activeIndex = i" @click="keywords !== '' && saveKeyword()">
                            <span class="ml-suggest-thumb">
                                <img v-if="item.image" :src="item.image" alt="" loading="lazy">
                                <i v-else class="fas fa-book"></i>
                            </span>
                            <span class="ml-suggest-text">
                                <strong v-html="highlight(item.title)"></strong>
                                <small>
                                    <i class="fas fa-user mr-1"></i><span v-html="highlight(item.authors || '—')"></span>
                                    <template v-if="item.year"> &bull; {{ item.year }}</template>
                                </small>
                            </span>
                            <code v-if="item.call_number" class="ml-suggest-call">{{ item.call_number }}</code>
                        </a>
                    </li>
                </ul>
                <a v-if="suggestions.length" href="javascript:void(0)" class="ml-suggest-all" @click="searchSubmit">
                    <?= mlT('Lihat semua hasil untuk', 'See all results for') ?> “{{ keywords }}” <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- ===== riwayat pencarian ===== -->
            <template v-if="lastKeywords.length > 0">
                <p class="ml-panel-label mt-3 d-flex justify-content-between align-items-center">
                    <span><i class="far fa-clock mr-1"></i><?= mlT('Pencarian terakhir kamu', 'Your recent searches'); ?>:</span>
                    <a href="javascript:void(0)" class="ml-history-clear" @click="clearHistory"><?= mlT('Hapus riwayat', 'Clear history') ?></a>
                </p>
                <div class="d-flex flex-wrap">
                    <a :href="historyUrl(tmpObj[k])" class="ml-chip ml-chip-history" v-for="k in lastKeywords" :key="k">
                        <i class="far fa-clock mr-2"></i>{{ tmpObj[k] ? tmpObj[k].text : '' }}
                    </a>
                </div>
            </template>

            <a class="ml-panel-all" href="index.php?keywords=&search=search">
                <i class="fas fa-book mr-2"></i><?= sprintf(mlT('Jelajahi seluruh %s koleksi', 'Browse all %s collections'), number_format($ml_total_docs, 0, ',', '.')) ?>
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </transition>
</div>
