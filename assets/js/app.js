/**
 * @Author: Waris Agung Widodo <user>
 * @Date:   2018-01-21T12:15:49+07:00
 * @Email:  ido.alit@gmail.com
 * @Filename: app.js
 * @Last modified by:   user
 * @Last modified time: 2018-01-23T18:22:24+07:00
 */

'use strict';

Vue.directive('click-outside', {
    priority: 700,
    bind: function (el, binding, vnode) {
        window.event = function (event) {
            if (!(el === event.target || el.contains(event.target))) {
                vnode.context[binding.expression](event);
            }
        };
        document.body.addEventListener('click', window.event)
    },
    unbind: function (el) {
        document.body.removeEventListener('click', window.event)
    },
});

// ----------------------------------------------------------------------------
// Modern Library: kotak pencarian (fitur ala Pustaka Ceria)
// - pilih kolom: Semua / Judul / Pengarang / Subjek / ISBN-ISSN / Penerbit
// - kata kunci & kolom tetap terisi di halaman hasil
// - riwayat pencarian (localStorage) + tombol hapus riwayat
// ----------------------------------------------------------------------------
const mlSearchEl = document.getElementById('search-wraper')
const mlSearchFields = ['keywords', 'title', 'author', 'subject', 'isbn', 'publisher', 'callnumber']
const show_advanced = new Vue({
    el: '#search-wraper',
    data: function () {
        const ds = (mlSearchEl && mlSearchEl.dataset) || {}
        return {
            show: false,
            isFocus: false,
            searchBy: mlSearchFields.indexOf(ds.field) > -1 ? ds.field : 'keywords',
            keywords: ds.value || '',
            tmpObj: {},
            historyTick: 0,
            // saran koleksi langsung
            suggestions: [],
            suggestLoading: false,
            suggestQuery: '',
            activeIndex: -1
        }
    },
    watch: {
        keywords: function () { this.queueSuggest() },
        searchBy: function () { this.queueSuggest(true) }
    },
    computed: {
        lastKeywords: function () {
            this.historyTick // dependensi agar daftar ter-update setelah riwayat dihapus
            let raw = null
            try { raw = localStorage.getItem('keywords') } catch (e) { return [] }
            if (raw) {
                try {
                    let keywords = JSON.parse(raw), arr = []
                    for (let key in keywords) {
                        if (keywords.hasOwnProperty(key)) {
                            arr.push(keywords[key].time)
                            keywords[key].text = key
                            this.tmpObj[keywords[key].time] = keywords[key]
                        }
                    }
                    arr.sort()
                    arr.reverse()
                    return arr.slice(0, 5)
                } catch (e) {
                    console.error(e.message)
                    return []
                }
            }
            return []
        }
    },
    methods: {
        searchOnFocus: function () {
            this.show = true;
            this.isFocus = true;
            if (this.keywords.length >= 2 && this.suggestQuery !== this.keywords) this.queueSuggest(true)
        },
        searchOnBlur: function () {
            this.isFocus = false
        },
        hideSearch: function () {
            if (!this.isFocus) this.show = false;
        },
        // pilih kolom pencarian; langsung cari bila kata kunci sudah diisi
        setField: function (field) {
            this.searchBy = field
            if (this.keywords !== '') return this.searchSubmit()
            this.$nextTick(() => this.$refs.keywords && this.$refs.keywords.focus())
        },
        searchOnClick: function (searchBy) {
            this.setField(searchBy)
        },
        historyUrl: function (item) {
            const field = item && mlSearchFields.indexOf(item.searchBy) > -1 ? item.searchBy : 'keywords'
            return `index.php?${field}=${encodeURIComponent(item ? item.text : '')}&search=search`
        },
        clearHistory: function () {
            try { localStorage.removeItem('keywords') } catch (e) {}
            this.historyTick++
        },
        // ---- saran koleksi langsung ----
        queueSuggest: function (immediate) {
            clearTimeout(this._suggestTimer)
            const q = this.keywords
            if (q.length < 2) {
                this.suggestions = []; this.suggestQuery = ''; this.activeIndex = -1; this.suggestLoading = false
                return
            }
            this.suggestLoading = true
            this._suggestTimer = setTimeout(() => this.fetchSuggest(q), immediate ? 0 : 250)
        },
        fetchSuggest: function (q) {
            const field = this.searchBy
            const ticket = (this._suggestTicket = (this._suggestTicket || 0) + 1)
            fetch(`index.php?ml_suggest=${encodeURIComponent(q)}&field=${field}`, {headers: {'Accept': 'application/json'}})
                .then(res => res.json())
                .then(data => {
                    if (ticket !== this._suggestTicket) return // abaikan respons lama
                    this.suggestions = Array.isArray(data) ? data : []
                    this.suggestQuery = q
                    this.activeIndex = -1
                    if (this.isFocus) this.show = true
                })
                .catch(() => { if (ticket === this._suggestTicket) this.suggestions = [] })
                .finally(() => { if (ticket === this._suggestTicket) this.suggestLoading = false })
        },
        moveActive: function (step) {
            if (!this.show) this.show = true
            const n = this.suggestions.length
            if (!n) return
            let i = this.activeIndex + step          // -1 = kembali ke kotak input
            if (i < -1) i = n - 1
            if (i >= n) i = -1
            this.activeIndex = i
            this.$nextTick(() => {
                const el = this.$el.querySelector('.ml-suggest-item.is-active')
                if (el) el.scrollIntoView({block: 'nearest'})
            })
        },
        onEnter: function () {
            const item = this.suggestions[this.activeIndex]
            if (this.activeIndex > -1 && item) {
                if (this.keywords !== '') this.saveKeyword()
                window.location.href = `index.php?p=show_detail&id=${item.id}`
            } else {
                this.searchSubmit()
            }
        },
        highlight: function (text) {
            const esc = s => String(s || '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]))
            const safe = esc(text)
            const q = (this.suggestQuery || '').trim()
            if (q.length < 2) return safe
            const re = new RegExp('(' + esc(q).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig')
            return safe.replace(re, '<mark>$1</mark>')
        },
        searchSubmit: function () {
            if (this.keywords !== '') this.saveKeyword()
            window.location.href = `index.php?${this.searchBy}=${encodeURIComponent(this.keywords)}&search=search`;
        },
        saveKeyword: function () {
            let keywords = {};
            try {
                const rawKeywords = localStorage.getItem('keywords')
                if (rawKeywords) keywords = JSON.parse(rawKeywords)
            } catch (e) {
                console.error(e.message)
            }
            if (keywords.hasOwnProperty(this.keywords)) {
                keywords[this.keywords] = {
                    count: keywords[this.keywords].count + 1,
                    searchBy: this.searchBy,
                    time: Date.now()
                }
            } else {
                keywords[this.keywords] = {
                    count: 1,
                    searchBy: this.searchBy,
                    time: Date.now()
                }
            }
            try { localStorage.setItem('keywords', JSON.stringify(keywords)) } catch (e) {}
        }
    }
});

Vue.component('slims-subject', {
    props: {
        topic: {
            type: String,
            default: ''
        }
    },
    render: function(createElement) {
        return createElement('a', {
            domProps: {
                innerHTML: this.topic
            },
            attrs: {
                href: `index.php?subject="${encodeURIComponent(this.topic).replace(/%20/g, "+")}"&search=search`,
                class: 'btn btn-outline-secondary btn-rounded btn-sm mr-2 mb-2'
            }
        })
    }
});

Vue.component('slims-book', {
    props: {
        biblioId: {
            type: String,
            default: ''
        },
        title: {
            type: String,
            default: ''
        },
        image: {
            type: String,
            default: ''
        },
        badge: {
            type: String,
            default: ''
        }
    },
    // Modern Library: kartu bergaya "product card"
    render: function (createElement) {
        const url = `index.php?p=show_detail&id=${this.biblioId}`
        const cover = [
            createElement('img', {
                attrs: {
                    src: this.image.replace(/width=\d+/, 'width=300'),
                    alt: '',
                    loading: 'lazy'
                }
            })
        ]
        if (this.badge) {
            cover.unshift(createElement('span', {
                attrs: {class: 'ml-book-badge'},
                domProps: {innerHTML: this.badge}
            }))
        }
        return createElement('div', {
            attrs: {class: 'ml-book'}
        }, [
            createElement('a', {
                attrs: {href: url, class: 'ml-book-card'}
            }, [
                createElement('div', {attrs: {class: 'ml-book-cover'}}, cover),
                createElement('div', {attrs: {class: 'ml-book-body'}}, [
                    createElement('div', {
                        attrs: {class: 'ml-book-title'},
                        domProps: {innerHTML: this.title}
                    }),
                    createElement('div', {attrs: {class: 'ml-book-foot'}}, [
                        createElement('span', {
                            attrs: {class: 'ml-book-more'},
                            domProps: {innerHTML: (document.documentElement.lang || 'id') === 'id' ? 'Lihat detail' : 'View detail'}
                        }),
                        createElement('span', {
                            attrs: {class: 'ml-book-go'},
                            domProps: {innerHTML: '<i class="fas fa-arrow-right"></i>'}
                        })
                    ])
                ])
            ])
        ])
    }
});

Vue.component('slims-member', {
    props: {
        image: {
            type: String,
            default: ''
        },
        memberName: {
            type: String,
            default: ''
        },
        memberType: {
            type: String,
            default: ''
        },
        totalLoan: {
            type: String,
            default: '0'
        },
        totalBiblio: {
            type: String,
            default: '0'
        }
    },
    render: function (createElement) {
        return createElement('div', {
            attrs: {
                class: 'w-full md:w-1/3 px-3 mb-2'
            }
        }, [
            createElement('div', {
                attrs: {
                    class: 'card hover:shadow-md'
                }
            }, [
                createElement('div', {
                    attrs: {
                        class: 'card-body'
                    }
                }, [
                    createElement('div', {
                        attrs: {
                            class: 'card-image-rounded mx-auto'
                        }
                    }, [
                        createElement('img', {
                            attrs: {
                                class: 'img-fluid h-auto',
                                src: this.image,
                                loading: 'lazy'
                            }
                        })
                    ]),
                    createElement('h5', {
                        attrs: {
                            class: 'card-title text-center mt-3'
                        }
                    }, [
                        createElement('span', this.memberName),
                        createElement('br'),
                        createElement('small', {
                            attrs: {
                                class: 'text-grey-darker'
                            },
                            domProps: {
                                innerHTML: this.memberType
                            }
                        })
                    ]),
                    createElement('p', {
                        attrs: {
                            class: 'card-text text-center'
                        }
                    }, [
                        createElement('b', this.totalLoan),
                        createElement('span', {
                            attrs: {
                                class: 'text-grey-darker ml-1'
                            },
                            domProps: {
                                innerHTML: 'Loans'
                            }
                        }),
                        createElement('span', {
                            attrs: {
                                class: 'inline-block h-4 mx-3 relative bg-grey align-middle',
                                style: 'width: 1px'
                            }
                        }),
                        createElement('b', this.totalBiblio),
                        createElement('span', {
                            attrs: {
                                class: 'text-grey-darker ml-1'
                            },
                            domProps: {
                                innerHTML: 'Title'
                            }
                        }),
                    ])
                ])
            ])
        ]);
    }
});

Vue.component('slims-collection', {
    props: {
        url: {
            type: String,
            default: ''
        },
        badge: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            biblios: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.biblios = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.biblios.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            const badge = this.badge
            if (!this.loading && (!Array.isArray(this.biblios) || this.biblios.length < 1)) {
                return createElement('div', {
                    attrs: {class: 'ml-empty'},
                    domProps: {innerHTML: '<i class="fas fa-book"></i> ' + ((document.documentElement.lang || 'id') === 'id' ? 'Belum ada koleksi untuk ditampilkan.' : 'No collections to show yet.')}
                })
            }
            return createElement('div', {
                attrs: {
                    class: 'collection ml-collection'
                }
            }, this.biblios.map(function (item) {
                return createElement('slims-book', {
                    attrs: {
                        biblioId: String(item.biblio_id),
                        image: item.image,
                        title: item.title,
                        badge: badge,
                    }
                })
            }))
        }
    }
});

Vue.component('slims-group-subject', {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            subjects: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.subjects = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.subjects.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            return createElement('div', {
                attrs: {
                    class: 'flex flex-wrap'
                }
            }, this.subjects.map(function (topic) {
                return createElement('slims-subject', {
                    attrs: {
                        topic
                    }
                })
            }))
        }
    }
});

Vue.component('slims-group-member', {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            members: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.members = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.members.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            return createElement('div', {
                attrs: {
                    class: 'flex flex-wrap'
                }
            }, this.members.map(function (member) {
                return createElement('slims-member', {
                    attrs: {
                        memberName: member.name,
                        memberType: member.type,
                        image: member.image,
                        totalLoan: member.total,
                        totalBiblio: member.total_title
                    }
                })
            }))
        }
    }
});

if (document.getElementById('slims-home')) {
    const slimsHome = new Vue({
        el: '#slims-home',
    })
}