<?php
/**
 * Template by          : Tutorial SLiMS
 * Template name        : Modern Library — SLiMS 9.8.0 OPAC theme (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Based on SLiMS default template (GPL-3.0).
 */
/**
 * Modern Library — halaman Buku Tamu (index.php?p=visitor)
 * Tampil di dalam header & footer beranda: hero krem (sambutan, jam, statistik)
 * + panel hijau berisi formulir check-in, panduan, kutipan, dan banner promo.
 * Proses check-in tetap memakai endpoint bawaan SLiMS (CSRF, parameter room, suara).
 * Dibangun dari visitor_template.php bawaan (Waris Agung Widodo).
 */

// pembungkus halaman (dibutuhkan lib/contents/visitor.inc.php)
$main_template_path = __DIR__ . '/login_template.inc.php';

// set default language
if (isset($_GET['select_lang'])) {
    $select_lang = trim(strip_tags($_GET['select_lang']));
    $cookie_opt = ['path' => SWB, 'domain' => '', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax'];
    if (isset($_COOKIE['select_lang'])) {
        @setcookie('select_lang', $select_lang, ['expires' => time() - 14400] + $cookie_opt);
    }
    @setcookie('select_lang', $select_lang, ['expires' => time() + 14400] + $cookie_opt);
    $sysconf['default_lang'] = $select_lang;
} else if (isset($_COOKIE['select_lang'])) {
    $sysconf['default_lang'] = trim(strip_tags($_COOKIE['select_lang']));
}

// teks dwibahasa sederhana (classic.php belum dimuat di tahap ini)
$cv_id = stripos($sysconf['default_lang'] ?? 'id_ID', 'id') === 0;
$cvT = fn($id, $en) => $cv_id ? $id : $en;
$cvH = fn($s) => htmlspecialchars(strip_tags((string)$s), ENT_QUOTES, 'UTF-8', false);

// statistik kunjungan dari tabel visitor_count
$cv_count = function ($sql) use ($dbs) {
    $q = $dbs->query($sql);
    return ($q && ($r = $q->fetch_row())) ? (int)$r[0] : 0;
};
$cv_today = $cv_count("SELECT COUNT(*) FROM visitor_count WHERE checkin_date >= CURDATE()");
$cv_month = $cv_count("SELECT COUNT(*) FROM visitor_count WHERE checkin_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$cv_total = $cv_count("SELECT COUNT(*) FROM visitor_count");
$cv_num = fn($n) => number_format($n, 0, ',', '.');

// logo perpustakaan
$cv_logo = '<i class="fas fa-book" aria-hidden="true"></i>';
$cv_disk = \SLiMS\Filesystems\Storage::images();
if (!empty($sysconf['logo_image']) && $cv_disk->isExists($cv_path = 'default/' . $sysconf['logo_image'])) {
    $cv_logo = '<img src="' . SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $cv_path . '&width=120" alt="logo">';
}

$cv_room = isset($_GET['room']) ? '&room=' . simbio_security::xssFree($_GET['room']) : '';
$cv_locale = str_replace('_', '-', $sysconf['default_lang'] ?? 'id_ID');
?>
<main class="ml-main mv-main">
    <!-- ================= HERO BUKU TAMU (gaya hero beranda) ================= -->
    <section class="container ml-section-first">
        <div class="ml-hero mv-hero" id="visitor-counter" v-cloak>
            <div class="ml-hero-text">
                <transition name="mv-pop" mode="out-in">
                    <!-- kartu hasil check-in -->
                    <div v-if="textInfo !== ''" key="result" class="mv-result" :class="'is-' + state" role="status">
                        <div class="mv-result-photo">
                            <img :src="image" alt="" :class="{'is-guest': isGuest}" @error="onImageError">
                            <span class="mv-result-badge">
                                <i :class="state === 'success' ? 'fas fa-check' : (state === 'warning' ? 'fas fa-exclamation' : 'fas fa-times')"></i>
                            </span>
                        </div>
                        <div>
                            <small>{{ state === 'success' ? '<?= $cvT('Check-in berhasil', 'Checked in') ?>' : (state === 'warning' ? '<?= $cvT('Perhatian', 'Attention') ?>' : '<?= $cvT('Gagal', 'Failed') ?>') }}</small>
                            <h2>{{ textInfo }}</h2>
                            <p><i class="far fa-clock mr-1"></i>{{ stamp }}</p>
                        </div>
                    </div>

                    <div v-else key="welcome">
                        <span class="ml-eyebrow"><i class="fas fa-leaf"></i> <?= $cvT('Buku Tamu', 'Guest Book') ?> &middot; <?= $cvH($sysconf['library_name']) ?></span>
                        <h1 class="ml-hero-title"><?= $cvT('Selamat Datang, Silakan Isi Buku Tamu', 'Welcome, Please Sign the Guest Book') ?></h1>
                        <p class="ml-hero-desc"><?= $cvT('Catat kunjungan Anda hari ini. Anggota cukup memasukkan ID anggota, tamu umum mengisi nama dan asal institusi.',
                                                         'Record your visit today. Members only need their member ID; guests fill in their name and institution.') ?></p>
                    </div>
                </transition>

                <ul class="ml-trust mv-trust">
                    <li><i class="far fa-clock"></i><span><strong class="mv-clock">{{ clock }}</strong>{{ today }}</span></li>
                    <li><i class="fas fa-user"></i><span><strong>{{ fmt(todayCount) }}</strong><?= $cvT('Pengunjung hari ini', 'Visitors today') ?></span></li>
                    <li><i class="far fa-calendar-alt"></i><span><strong><?= $cv_num($cv_month) ?></strong><?= $cvT('Bulan ini', 'This month') ?></span></li>
                    <li><i class="fas fa-users"></i><span><strong><?= $cv_num($cv_total) ?></strong><?= $cvT('Total kunjungan', 'Total visits') ?></span></li>
                </ul>
            </div>

            <!-- panel hijau berisi formulir (posisi etalase buku di beranda) -->
            <div class="ml-hero-visual mv-visual">
                <div class="mv-formwrap">
                    <div class="mv-card">
                        <div class="mv-card-head">
                            <span class="mv-card-icon"><i class="fas fa-edit"></i></span>
                            <div>
                                <h2><?= $cvT('Isi Buku Tamu', 'Sign the Guest Book') ?></h2>
                                <p class="mv-muted"><?= __('Please fill your member ID or name.') ?></p>
                            </div>
                        </div>

                        <form class="mv-form" @submit.prevent="onSubmit" autocomplete="off">
                            <label class="mv-label" for="mv-member"><?= $cvT('ID Anggota / Nama Tamu', 'Member ID / Guest Name') ?></label>
                            <div class="mv-field">
                                <i class="far fa-id-card"></i>
                                <input v-model="memberId" ref="memberId" autofocus type="text" id="mv-member" maxlength="100"
                                       placeholder="<?= __('Enter your member ID') ?>">
                                <button type="button" class="mv-clear" v-if="memberId" @click="memberId = ''; $refs.memberId.focus()" aria-label="<?= $cvT('Hapus', 'Clear') ?>"><i class="fas fa-times"></i></button>
                            </div>

                            <label class="mv-label" for="mv-inst"><?= __('Institution') ?> <span class="mv-opt"><?= $cvT('(wajib untuk tamu)', '(required for guests)') ?></span></label>
                            <div class="mv-field">
                                <i class="fas fa-university"></i>
                                <input v-model="institution" type="text" id="mv-inst" maxlength="100" placeholder="<?= __('Enter your institution') ?>">
                            </div>
                            <small class="mv-hint"><i class="fas fa-info-circle"></i> <?= __('Enough fill your member ID if you are member of ') . $cvH($sysconf['library_name']) ?></small>

                            <button type="submit" class="mv-submit" :disabled="loading">
                                <span v-if="loading" class="ml-search-spin"></span>
                                <i v-else class="fas fa-sign-in-alt"></i>
                                <?= __('Check In') ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="ml-hero-badge mv-badge">
                    <small><?= $cvT('Hari ini', 'Today') ?></small>
                    <strong>{{ fmt(todayCount) }}</strong>
                    <small><?= $cvT('pengunjung', 'visitors') ?></small>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= PANDUAN + KUTIPAN ================= -->
    <section class="container ml-section">
        <div class="mv-guide">
            <div class="ml-panel mv-steps">
                <h3 class="mv-subtitle"><?= $cvT('Cara mengisi buku tamu', 'How to sign the guest book') ?></h3>
                <ol>
                    <li><span>1</span><div><strong><?= $cvT('Anggota', 'Members') ?></strong><?= $cvT('Ketik atau pindai ID anggota, lalu tekan Enter.', 'Type or scan your member ID, then press Enter.') ?></div></li>
                    <li><span>2</span><div><strong><?= $cvT('Tamu umum', 'Guests') ?></strong><?= $cvT('Isi nama lengkap dan asal institusi.', 'Fill in your full name and institution.') ?></div></li>
                    <li><span>3</span><div><strong><?= $cvT('Selesai', 'Done') ?></strong><?= $cvT('Kartu sambutan muncul dan kunjungan tercatat.', 'A welcome card appears and your visit is recorded.') ?></div></li>
                </ol>
            </div>
            <div class="ml-panel mv-quote-card" id="visitor-quote">
                <i class="fas fa-quote-left"></i>
                <p id="mv-quote-text"><?= $cvT('Membaca adalah jendela dunia.', 'Reading is a window to the world.') ?></p>
                <footer id="mv-quote-author">— <?= $cvH($sysconf['library_name']) ?></footer>
            </div>
        </div>
    </section>

    <!-- ================= BANNER (sama seperti beranda) ================= -->
    <section class="container ml-section">
        <div class="ml-promos">
            <a class="ml-promo ml-promo-green" href="index.php?keywords=&amp;search=search">
                <div>
                    <span class="ml-promo-kicker"><?= $cvT('Katalog', 'Catalog') ?></span>
                    <h3><?= $cvT('Cari Koleksi Perpustakaan', 'Search the Collection') ?></h3>
                    <span class="ml-promo-link"><?= $cvT('Jelajahi koleksi', 'Browse collection') ?> <i class="fas fa-arrow-right"></i></span>
                </div>
                <span class="ml-promo-art"><i class="fas fa-book"></i></span>
            </a>
            <a class="ml-promo ml-promo-peach" href="index.php?p=member">
                <div>
                    <span class="ml-promo-kicker"><?= __('Member Area') ?></span>
                    <h3><?= $cvT('Simpan & Pinjam Favoritmu', 'Save & Borrow Your Favorites') ?></h3>
                    <span class="ml-promo-link"><?= $cvT('Masuk sekarang', 'Sign in now') ?> <i class="fas fa-arrow-right"></i></span>
                </div>
                <span class="ml-promo-art"><i class="fas fa-bookmark"></i></span>
            </a>
            <a class="ml-promo ml-promo-blue" href="index.php?p=libinfo">
                <div>
                    <span class="ml-promo-kicker"><?= __('Information') ?></span>
                    <h3><?= $cvT('Kenali Layanan Perpustakaan', 'Discover Our Services') ?></h3>
                    <span class="ml-promo-link"><?= $cvT('Selengkapnya', 'Learn more') ?> <i class="fas fa-arrow-right"></i></span>
                </div>
                <span class="ml-promo-art"><i class="fas fa-university"></i></span>
            </a>
        </div>
    </section>
</main>

<script src="<?php echo $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/assets/js/axios.min.js'; ?>"></script>
<script src="<?= JWB . 'he.js' ?>"></script>
<script>
    new Vue({
        el: '#visitor-counter',
        data() {
            return {
                memberId: '',
                institution: '',
                textInfo: '',
                state: 'success',
                stamp: '',
                loading: false,
                todayCount: <?= (int)$cv_today ?>,
                image: './images/persons/photo.png',
                isGuest: false,
                // foto untuk pengunjung yang bukan anggota (tamu umum)
                guestImage: <?= json_encode($sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/assets/images/guest-visitor.jpg') ?>,
                quotes: {},
                timeout: null,
                clock: '',
                today: '',
                csrfName: '<?= \Volnix\CSRF\CSRF::getTokenName() ?>',
                csrfToken: '<?= \Volnix\CSRF\CSRF::getToken() ?>'
            }
        },
        watch: {
            // kutipan ditampilkan di panel di luar area Vue
            quotes: function (q) {
                if (!q || !q.content) return
                var t = document.getElementById('mv-quote-text'), a = document.getElementById('mv-quote-author')
                if (t) t.textContent = q.content
                if (a) a.textContent = '— ' + (q.author || '')
            }
        },
        mounted() {
            this.$refs.memberId.focus()
            this.getQuotes()
            this.tick()
            setInterval(this.tick, 1000)
        },
        methods: {
            fmt: function (n) {
                return Number(n).toLocaleString('id-ID')
            },
            tick: function () {
                var now = new Date(), loc = '<?= $cv_locale ?>'
                try {
                    this.clock = now.toLocaleTimeString(loc, {hour: '2-digit', minute: '2-digit', second: '2-digit'})
                    this.today = now.toLocaleDateString(loc, {weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'})
                } catch (e) {
                    this.clock = now.toTimeString().slice(0, 8)
                    this.today = now.toDateString()
                }
            },
            onImageError: function () {
                if (this.isGuest) return
                this.image = './images/persons/photo.png'
            },
            getQuotes: function () {
                axios.get('https://slims.web.id/kutipan/')
                    .then(res => {
                        res.data.content = he.decode(res.data.content)
                        this.quotes = res.data
                    })
                    .catch(() => {
                        this.quotes = {
                            content: <?= json_encode($cvT('Membaca adalah jendela dunia.', 'Reading is a window to the world.')) ?>,
                            author: <?= json_encode($cvH($sysconf['library_name'])) ?>
                        }
                    })
                    .finally(() => {
                        this.textInfo = ''
                    })
            },
            onSubmit: function () {
                if (this.memberId.trim() === '' || this.loading) {
                    this.resetForm()
                    return
                }
                let url = 'index.php?p=visitor<?= $cv_room ?>'
                let data = new FormData()
                data.append('memberID', this.memberId)
                data.append('institution', this.institution)
                data.append('counter', 1)
                data.append(this.csrfName, this.csrfToken)
                this.loading = true

                axios({
                    url: url,
                    method: 'post',
                    data: data,
                    headers: {'Content-Type': 'multipart/form-data', 'X-Response-With': 'application/json'}
                })
                    .then(res => {
                        var d = res.data || {}
                        var raw = String(d.message || '')
                        // hasil: gagal (status berisi pesan error), peringatan (kedaluwarsa / institusi kosong), berhasil
                        var instMsg = <?= json_encode(__('Sorry, Please fill institution field if you are not library member')) ?>;
                        var backMsg = <?= json_encode(__('Welcome back')) ?>;
                        if (d.already_today) {
                            // plugin "Batas Buku Tamu Harian Anggota": anggota sudah mengisi hari ini
                            this.state = 'warning'
                        } else if (raw.trim() === instMsg) {
                            this.state = 'warning'
                        } else if (d.status) {
                            this.state = 'error'
                        } else if (/visitor-error/.test(raw)) {
                            this.state = 'warning'
                        } else {
                            this.state = 'success'
                            if (raw.indexOf(backMsg) !== 0) this.todayCount++
                        }
                        // tampilkan sebagai teks biasa (nama tamu diketik bebas oleh pengunjung)
                        var tmp = document.createElement('div')
                        tmp.innerHTML = raw.replace(/<script[\s\S]*?<\/script>/gi, '')
                        this.textInfo = (tmp.textContent || '').trim()
                        // Logo Tutorial SLiMS dipakai untuk:
                        //  - tamu yang bukan anggota (SLiMS memberi nama gambar "non_member.png")
                        //  - salah input (institusi kosong untuk tamu, atau gagal/ditolak)
                        // Anggota (termasuk yang kedaluwarsa) tetap memakai fotonya sendiri.
                        var inputError = raw.trim() === instMsg || this.state === 'error'
                        this.isGuest = d.image === 'non_member.png' || inputError
                        this.image = this.isGuest
                            ? this.guestImage
                            : './images/persons/' + encodeURIComponent(d.image || 'photo.png')
                        this.stamp = this.today + ' · ' + this.clock
                        if (d.new_token) this.csrfToken = d.new_token
                        <?php if ($sysconf['template']['visitor_log_voice']) : ?>
                        this.textToSpeech(this.textInfo)
                        <?php endif; ?>
                    })
                    .catch(err => {
                        console.log(err)
                        if (err.response && err.response.data && err.response.data.new_token) {
                            this.csrfToken = err.response.data.new_token
                        }
                        this.state = 'error'
                        this.isGuest = true
                        this.image = this.guestImage
                        this.stamp = this.today + ' · ' + this.clock
                        this.textInfo = <?= json_encode($cvT('Terjadi kesalahan. Silakan coba lagi.', 'Something went wrong. Please try again.')) ?>;
                    })
                    .finally(() => {
                        this.loading = false
                        this.resetForm()
                        clearTimeout(this.timeout)
                        this.timeout = setTimeout(() => {
                            this.getQuotes()
                        }, 6000)
                    })
            },
            resetForm: function () {
                this.memberId = ''
                this.institution = ''
                this.$refs.memberId.focus()
            },
            textToSpeech: function (text) {
                if (!('speechSynthesis' in window) || !text) return
                var message = new SpeechSynthesisUtterance(text)
                message.volume = 1
                message.rate = 1
                message.pitch = 1
                message.lang = '<?= $cv_locale ?>'
                speechSynthesis.cancel()
                speechSynthesis.speak(message)
            }
        }
    })
</script>
