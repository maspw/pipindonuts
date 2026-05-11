<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Kasir — Pipindonuts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0d0f14;--surface:#161b27;--surface2:#1e2535;--border:#2a3145;--accent:#f97316;--accent-hover:#ea6c0a;--text:#e8eaf0;--muted:#8892a4;--success:#22c55e;--danger:#ef4444;--radius:12px}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);height:100vh;overflow:hidden}

/* ── TOP BAR ── */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:var(--surface);border-bottom:1px solid var(--border);height:60px;flex-shrink:0}
.topbar-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px;color:var(--accent)}
.topbar-logo span{font-size:22px}
.topbar-info{display:flex;align-items:center;gap:16px;font-size:13px;color:var(--muted)}
.topbar-info strong{color:var(--text)}
.topbar-time{font-size:14px;font-weight:600;color:var(--text);background:var(--surface2);padding:6px 14px;border-radius:8px;border:1px solid var(--border)}

/* ── LAYOUT ── */
.pos-body{display:flex;height:calc(100vh - 60px)}

/* ── PRODUCT PANEL ── */
.product-panel{flex:1;display:flex;flex-direction:column;overflow:hidden;padding:16px}
.search-bar{position:relative;margin-bottom:14px}
.search-bar input{width:100%;padding:11px 16px 11px 42px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-size:14px;outline:none;transition:.2s}
.search-bar input:focus{border-color:var(--accent)}
.search-bar .icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:16px}

.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;overflow-y:auto;padding-right:4px}
.product-grid::-webkit-scrollbar{width:4px}
.product-grid::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}

.product-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;cursor:pointer;transition:transform .15s,border-color .15s,box-shadow .15s;position:relative}
.product-card:hover{transform:translateY(-2px);border-color:var(--accent);box-shadow:0 4px 20px rgba(249,115,22,.2)}
.product-card:active{transform:scale(.97)}
.product-card.out-of-stock{opacity:.4;cursor:not-allowed;pointer-events:none}
.product-card img{width:100%;height:120px;object-fit:cover;display:block}
.product-card .no-img{width:100%;height:120px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:36px}
.product-card .info{padding:10px}
.product-card .name{font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px;line-height:1.3}
.product-card .price{font-size:14px;font-weight:700;color:var(--accent)}
.product-card .stok-badge{position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);color:#fff;font-size:10px;padding:2px 7px;border-radius:20px;backdrop-filter:blur(4px)}
.product-card .stok-badge.low{background:rgba(239,68,68,.8)}
.in-cart-badge{position:absolute;top:8px;left:8px;background:var(--accent);color:#fff;font-size:10px;font-weight:700;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center}

/* ── CART PANEL ── */
.cart-panel{width:320px;flex-shrink:0;background:var(--surface);border-left:1px solid var(--border);display:flex;flex-direction:column}
.cart-header{padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.cart-header h2{font-size:15px;font-weight:700}
.cart-clear{font-size:12px;color:var(--danger);cursor:pointer;border:none;background:none;color:var(--danger);padding:4px 8px;border-radius:6px;transition:.15s}
.cart-clear:hover{background:rgba(239,68,68,.1)}

.cart-items{flex:1;overflow-y:auto;padding:8px 12px}
.cart-items::-webkit-scrollbar{width:4px}
.cart-items::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
.cart-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;color:var(--muted)}
.cart-empty .icon{font-size:40px;opacity:.4}

.cart-item{display:flex;align-items:center;gap:10px;padding:10px 6px;border-bottom:1px solid var(--border)}
.cart-item img{width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0}
.cart-item .no-img-sm{width:44px;height:44px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.cart-item .detail{flex:1;min-width:0}
.cart-item .detail .name{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cart-item .detail .price{font-size:12px;color:var(--accent);margin-top:2px}
.cart-item .qty-ctrl{display:flex;align-items:center;gap:6px;flex-shrink:0}
.qty-btn{width:24px;height:24px;border-radius:6px;border:1px solid var(--border);background:var(--surface2);color:var(--text);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:.15s}
.qty-btn:hover{border-color:var(--accent);color:var(--accent)}
.qty-num{font-size:13px;font-weight:600;min-width:18px;text-align:center}

/* ── CART FOOTER ── */
.cart-footer{padding:14px 16px;border-top:1px solid var(--border)}
.summary-row{display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-bottom:6px}
.summary-total{display:flex;justify-content:space-between;font-size:17px;font-weight:800;color:var(--text);margin:10px 0}
.summary-total span:last-child{color:var(--accent)}
.pay-btn{width:100%;padding:14px;background:var(--accent);color:#fff;font-size:15px;font-weight:700;border:none;border-radius:var(--radius);cursor:pointer;transition:.15s;display:flex;align-items:center;justify-content:center;gap:8px}
.pay-btn:hover{background:var(--accent-hover);transform:translateY(-1px)}
.pay-btn:disabled{background:var(--border);color:var(--muted);cursor:not-allowed;transform:none}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(4px)}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:16px;width:420px;max-width:90vw;overflow:hidden}
.modal-head{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-head h3{font-size:16px;font-weight:700}
.modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:20px;line-height:1}
.modal-body{padding:24px}
.modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px}

/* Form elements */
.field-label{font-size:12px;color:var(--muted);margin-bottom:6px;display:block;font-weight:500}
.field-input{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;outline:none;transition:.2s;font-family:inherit}
.field-input:focus{border-color:var(--accent)}
.mb-16{margin-bottom:16px}

/* Payment method toggle */
.pay-methods{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:16px}
.pay-method{padding:10px;border:1px solid var(--border);border-radius:10px;cursor:pointer;text-align:center;transition:.15s;background:var(--surface2)}
.pay-method:hover{border-color:var(--accent)}
.pay-method.active{border-color:var(--accent);background:rgba(249,115,22,.1);color:var(--accent)}
.pay-method .icon{font-size:20px;margin-bottom:4px}
.pay-method .label{font-size:11px;font-weight:600}

/* Change display */
.change-box{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;margin-top:12px}
.change-box .change-label{font-size:11px;color:var(--muted);margin-bottom:4px}
.change-box .change-value{font-size:22px;font-weight:800;color:var(--success)}
.change-box .change-value.minus{color:var(--danger)}

/* Quick cash buttons */
.quick-cash{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin:10px 0}
.quick-btn{padding:7px 4px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:11px;font-weight:600;cursor:pointer;text-align:center;transition:.15s;font-family:inherit}
.quick-btn:hover{border-color:var(--accent);color:var(--accent);background:rgba(249,115,22,.08)}
.quick-btn.exact{border-color:var(--success);color:var(--success)}

/* Buttons */
.btn{padding:11px 20px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:.15s;font-family:inherit}
.btn-primary{background:var(--accent);color:#fff;flex:1}
.btn-primary:hover{background:var(--accent-hover)}
.btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{border-color:var(--accent);color:var(--accent)}
.btn:disabled{opacity:.4;cursor:not-allowed}

/* Notification */
.notif{position:fixed;top:16px;right:16px;background:var(--success);color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;z-index:200;animation:slideIn .3s ease}
@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}

/* No results */
.no-results{grid-column:1/-1;text-align:center;color:var(--muted);padding:40px;font-size:14px}
</style>
</head>
<body x-data="kasir()" x-init="startClock()">

{{-- ── TOP BAR ── --}}
<div class="topbar">
    <div class="topbar-logo">
        <span>🍩</span> Pipindonuts <span style="font-weight:400;color:var(--muted);font-size:14px;margin-left:4px">/ Kasir</span>
    </div>
    <div class="topbar-info">
        <span>Kasir: <strong>{{ auth()->user()->name }}</strong></span>
        <div class="topbar-time" x-text="waktu"></div>
        <form method="POST" action="{{ route('kasir.logout') }}" style="margin:0">
            @csrf
            <button type="submit" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;font-family:inherit">Keluar</button>
        </form>
    </div>
</div>

{{-- ── MAIN BODY ── --}}
<div class="pos-body">

    {{-- ── PRODUCT PANEL ── --}}
    <main class="product-panel">
        <div class="search-bar">
            <span class="icon">🔍</span>
            <input type="text" x-model="search" placeholder="Cari produk...">
        </div>

        <div class="product-grid">
            @forelse ($produk as $p)
            <div class="product-card {{ $p->stok <= 0 ? 'out-of-stock' : '' }}"
                 @click="addToCart({ id: {{ $p->id_produk }}, nama: '{{ addslashes($p->nama_produk) }}', harga: {{ $p->harga }}, stok: {{ $p->stok }}, gambar: '{{ $p->gambar ? asset('storage/'.$p->gambar) : '' }}' })"
                 x-show="search === '' || '{{ strtolower($p->nama_produk) }}'.includes(search.toLowerCase())">

                @if($p->gambar)
                    <img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->nama_produk }}">
                @else
                    <div class="no-img">🍩</div>
                @endif

                <span class="stok-badge {{ $p->stok <= 5 ? 'low' : '' }}">Stok: {{ $p->stok }}</span>

                <template x-if="cartQty({{ $p->id_produk }}) > 0">
                    <span class="in-cart-badge" x-text="cartQty({{ $p->id_produk }})"></span>
                </template>

                <div class="info">
                    <div class="name">{{ $p->nama_produk }}</div>
                    <div class="price">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                </div>
            </div>
            @empty
            <div class="no-results">Belum ada produk tersedia.</div>
            @endforelse
        </div>
    </main>

    {{-- ── CART PANEL ── --}}
    <aside class="cart-panel">
        <div class="cart-header">
            <h2>🛒 Pesanan</h2>
            <button class="cart-clear" @click="clearCart()" x-show="cart.length > 0">Kosongkan</button>
        </div>

        <div class="cart-items">
            <template x-if="cart.length === 0">
                <div class="cart-empty">
                    <div class="icon">🛒</div>
                    <span>Pilih produk untuk mulai</span>
                </div>
            </template>

            <template x-for="item in cart" :key="item.id">
                <div class="cart-item">
                    <template x-if="item.gambar">
                        <img :src="item.gambar" :alt="item.nama">
                    </template>
                    <template x-if="!item.gambar">
                        <div class="no-img-sm">🍩</div>
                    </template>
                    <div class="detail">
                        <div class="name" x-text="item.nama"></div>
                        <div class="price" x-text="'Rp ' + formatRp(item.harga * item.qty)"></div>
                    </div>
                    <div class="qty-ctrl">
                        <button class="qty-btn" @click="decQty(item.id)">−</button>
                        <span class="qty-num" x-text="item.qty"></span>
                        <button class="qty-btn" @click="incQty(item.id)" :disabled="item.qty >= item.stok">+</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span>Jumlah Item</span>
                <span x-text="totalItems + ' item'"></span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span x-text="'Rp ' + formatRp(total)"></span>
            </div>
            <button class="pay-btn" :disabled="cart.length === 0" @click="openPayment()">
                💳 Bayar Sekarang
            </button>
        </div>
    </aside>
</div>

{{-- ── PAYMENT MODAL ── --}}
<div class="modal-overlay" x-show="showPayment" x-transition @click.self="showPayment = false">
    <div class="modal">
        <div class="modal-head">
            <h3>💳 Pembayaran</h3>
            <button class="modal-close" @click="showPayment = false">✕</button>
        </div>
        <div class="modal-body">
            {{-- Metode Bayar --}}
            <label class="field-label">Metode Pembayaran</label>
            <div class="pay-methods mb-16">
                <div class="pay-method" :class="{ active: metodeBayar === 'tunai' }" @click="metodeBayar = 'tunai'; jumlahBayar = total">
                    <div class="icon">💵</div>
                    <div class="label">Tunai</div>
                </div>
                <div class="pay-method" :class="{ active: metodeBayar === 'transfer' }" @click="metodeBayar = 'transfer'; jumlahBayar = total">
                    <div class="icon">🏦</div>
                    <div class="label">Transfer</div>
                </div>
                <div class="pay-method" :class="{ active: metodeBayar === 'qris' }" @click="metodeBayar = 'qris'; jumlahBayar = total">
                    <div class="icon">📱</div>
                    <div class="label">QRIS</div>
                </div>
            </div>

            {{-- Total --}}
            <div class="summary-row mb-16" style="font-size:15px;font-weight:700;color:var(--text)">
                <span>Total Tagihan</span>
                <span style="color:var(--accent)" x-text="'Rp ' + formatRp(total)"></span>
            </div>

            {{-- Tunai: input + nominal cepat + kembalian --}}
            <template x-if="metodeBayar === 'tunai'">
                <div class="mb-16">
                    <label class="field-label">Uang Diterima dari Customer</label>
                    <div style="position:relative">
                        <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;font-weight:600">Rp</span>
                        <input type="number" class="field-input" style="padding-left:36px"
                            x-model.number="jumlahBayar" min="0" step="1000"
                            placeholder="0">
                    </div>

                    {{-- Tombol nominal cepat --}}
                    <div class="quick-cash">
                        <template x-for="nom in [1000,2000,5000,10000,20000,50000,100000]" :key="nom">
                            <button type="button" class="quick-btn"
                                :class="{ exact: jumlahBayar === nom }"
                                @click="jumlahBayar = nom"
                                x-text="nom >= 1000 ? (nom/1000)+'rb' : nom">
                            </button>
                        </template>
                        <button type="button" class="quick-btn exact"
                            @click="jumlahBayar = total"
                            title="Pas">
                            ✓ Pas
                        </button>
                    </div>

                    {{-- Kotak kembalian --}}
                    <div class="change-box">
                        <div class="change-label">💵 Kembalian</div>
                        <div class="change-value" :class="{ minus: kembalian < 0 }"
                            x-text="'Rp ' + formatRp(Math.abs(kembalian))">
                        </div>
                        <template x-if="kembalian < 0">
                            <div style="font-size:11px;color:var(--danger);margin-top:6px;font-weight:600">⚠️ Uang kurang Rp <span x-text="formatRp(Math.abs(kembalian))"></span></div>
                        </template>
                        <template x-if="kembalian === 0">
                            <div style="font-size:11px;color:var(--success);margin-top:6px">✅ Pas!</div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="metodeBayar !== 'tunai'">
                <div class="change-box mb-16">
                    <div class="change-label">Jumlah yang Harus Dibayar</div>
                    <div class="change-value" x-text="'Rp ' + formatRp(total)"></div>
                </div>
            </template>
        </div>

        <div class="modal-foot">
            <button class="btn btn-secondary" @click="showPayment = false">Batal</button>

            {{-- Tunai / Transfer: submit form biasa --}}
            <template x-if="metodeBayar !== 'qris'">
                <form method="POST" action="{{ route('kasir.transaksi') }}" style="flex:1">
                    @csrf
                    <input type="hidden" name="cart" :value="JSON.stringify(cart)">
                    <input type="hidden" name="metode_bayar" :value="metodeBayar">
                    <input type="hidden" name="jumlah_bayar" :value="jumlahBayar">
                    <button type="submit" class="btn btn-primary" style="width:100%"
                        :disabled="metodeBayar === 'tunai' && kembalian < 0">
                        ✅ Konfirmasi Bayar
                    </button>
                </form>
            </template>

            {{-- QRIS: lewat Midtrans Snap --}}
            <template x-if="metodeBayar === 'qris'">
                <button class="btn btn-primary" style="flex:1"
                    :disabled="loadingQris"
                    @click="bayarQris()">
                    <span x-show="!loadingQris">📱 Bayar dengan QRIS</span>
                    <span x-show="loadingQris">⏳ Memproses...</span>
                </button>
            </template>
        </div>
    </div>
</div>

{{-- Error notification --}}
@if(session('error'))
<div class="notif" style="background:var(--danger)" x-data x-init="setTimeout(() => $el.remove(), 3000)">
    ⚠️ {{ session('error') }}
</div>
@endif

<script>
function kasir() {
    return {
        cart: [],
        search: '',
        waktu: '',
        showPayment: false,
        metodeBayar: 'tunai',
        jumlahBayar: 0,
        loadingQris: false,

        get total() {
            return this.cart.reduce((s, i) => s + i.harga * i.qty, 0);
        },
        get totalItems() {
            return this.cart.reduce((s, i) => s + i.qty, 0);
        },
        get kembalian() {
            return this.jumlahBayar - this.total;
        },

        addToCart(produk) {
            const ex = this.cart.find(i => i.id === produk.id);
            if (ex) {
                if (ex.qty < ex.stok) ex.qty++;
            } else {
                this.cart.push({ ...produk, qty: 1 });
            }
        },
        incQty(id) {
            const item = this.cart.find(i => i.id === id);
            if (item && item.qty < item.stok) item.qty++;
        },
        decQty(id) {
            const item = this.cart.find(i => i.id === id);
            if (!item) return;
            if (item.qty <= 1) this.cart = this.cart.filter(i => i.id !== id);
            else item.qty--;
        },
        cartQty(id) {
            return this.cart.find(i => i.id === id)?.qty ?? 0;
        },
        clearCart() {
            this.cart = [];
        },
        openPayment() {
            if (this.cart.length === 0) return;
            this.jumlahBayar = this.total;
            this.metodeBayar = 'tunai';
            this.showPayment = true;
        },

        async bayarQris() {
            this.loadingQris = true;
            try {
                const res = await fetch('{{ route("kasir.midtrans.token") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ total: this.total }),
                });
                const data = await res.json();

                if (!data.success) {
                    alert('Gagal membuat pembayaran: ' + (data.message ?? 'Error'));
                    return;
                }

                // Buka Midtrans Snap popup
                const cart    = JSON.stringify(this.cart);
                const orderId = data.order_id;

                window.snap.pay(data.token, {
                    onSuccess: () => {
                        // Buat form dengan DOM API agar JSON tidak rusak oleh tanda kutip
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("kasir.transaksi") }}';

                        const fields = {
                            '_token'      : '{{ csrf_token() }}',
                            'cart'        : cart,
                            'metode_bayar': 'qris',
                            'jumlah_bayar': String(this.total),
                        };

                        Object.entries(fields).forEach(([name, value]) => {
                            const input = document.createElement('input');
                            input.type  = 'hidden';
                            input.name  = name;
                            input.value = value;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    },
                    onPending: () => {
                        alert('Pembayaran pending. Silakan selesaikan pembayaran QRIS.');
                    },
                    onError: (err) => {
                        alert('Pembayaran gagal: ' + JSON.stringify(err));
                    },
                    onClose: () => {
                        this.loadingQris = false;
                    },
                });
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.loadingQris = false;
            }
        },

        formatRp(n) {
            return Number(n).toLocaleString('id-ID');
        },
        startClock() {
            const tick = () => {
                const now = new Date();
                this.waktu = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            };
            tick();
            setInterval(tick, 1000);
        }
    };
}
</script>
</body>
</html>
