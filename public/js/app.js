/* =========================================================
   CaféNusa — Vanilla JavaScript
   Live search, preview gambar, toggle status, kalkulasi pesanan
   ========================================================= */
(function () {
    'use strict';

    /** Format angka ke rupiah. */
    function rupiah(angka) {
        return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
    }

    /** Tutup alert flash message. */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-dismiss]');
        if (btn) {
            var alert = btn.closest('.alert');
            if (alert) alert.remove();
        }
    });

    /* =======================================================
       1. Live search tanpa Enter / reload
       ======================================================= */
    document.querySelectorAll('[data-live-search]').forEach(function (input) {
        var targetSelector = input.getAttribute('data-live-search');
        var statusEl = document.querySelector(input.getAttribute('data-live-search-status') || '');

        function filter() {
            var q = input.value.trim().toLowerCase();
            var items = document.querySelectorAll(targetSelector);
            var visible = 0;

            items.forEach(function (item) {
                var text = (item.getAttribute('data-search-text') || item.textContent).toLowerCase();
                var show = text.indexOf(q) !== -1;
                item.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (statusEl) {
                statusEl.textContent = visible + ' data ditampilkan';
            }

            var empty = document.querySelector(input.getAttribute('data-live-search-empty') || '');
            if (empty) {
                empty.style.display = visible === 0 ? '' : 'none';
            }
        }

        input.addEventListener('input', filter);
    });

    /* =======================================================
       2. Preview gambar upload (langsung saat dipilih)
       ======================================================= */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        var preview = document.querySelector(input.getAttribute('data-preview'));

        input.addEventListener('change', function () {
            if (!preview) return;

            var file = input.files && input.files[0];
            if (!file) {
                preview.src = preview.getAttribute('data-placeholder') || '';
                return;
            }

            if (!/^image\/(jpeg|jpg|png|webp)$/.test(file.type)) {
                alert('Format gambar harus JPG, PNG, atau WebP.');
                input.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2 MB.');
                input.value = '';
                return;
            }

            preview.src = URL.createObjectURL(file);
        });
    });

    /* =======================================================
       3. Toggle aktif/nonaktif menu (AJAX / Fetch)
       ======================================================= */
    document.querySelectorAll('[data-toggle-menu]').forEach(function (el) {
        el.addEventListener('change', function () {
            var url = el.getAttribute('data-toggle-menu');
            var row = el.closest('tr');

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.success) throw new Error('Gagal');

                    var badge = row && row.querySelector('[data-active-badge]');
                    if (badge) {
                        badge.className = 'badge ' + (data.is_active ? 'badge-success' : 'badge-danger');
                        badge.textContent = data.is_active ? 'Aktif' : 'Nonaktif';
                    }
                })
                .catch(function () {
                    el.checked = !el.checked; // Kembalikan switch bila gagal.
                    alert('Gagal memperbarui status menu.');
                });
        });
    });

    /* =======================================================
       4. Pemesanan — total otomatis + batasi jumlah ke stok
       Selector memakai kelas .qty-input (input jumlah asli yang
       dikirim ke server), bukan [data-qty] yang menempel pada
       tombol - / +.
       ======================================================= */
    var qtyInputs = document.querySelectorAll('.qty-input');

    if (qtyInputs.length) {
        var totalEl = document.getElementById('cart-total');

        function hitungTotal() {
            var total = 0;

            qtyInputs.forEach(function (input) {
                var jumlah = parseInt(input.value, 10);
                if (isNaN(jumlah) || jumlah < 0) jumlah = 0;

                var stok = parseInt(input.getAttribute('data-stok'), 10) || 0;
                if (jumlah > stok) {
                    jumlah = stok;
                    input.value = stok;
                }

                var harga = parseInt(input.getAttribute('data-harga'), 10) || 0;
                total += jumlah * harga;
            });

            if (totalEl) totalEl.textContent = rupiah(total);
        }

        qtyInputs.forEach(function (input) {
            input.addEventListener('input', hitungTotal);
            input.addEventListener('change', hitungTotal);
        });

        hitungTotal();
    }

    /* =======================================================
       5. Form pemesanan — validasi sebelum submit
       Selector mengambil langsung semua <input> quantity di
       DALAM form pesanan: input[name*="[jumlah]"] (nama field
       items[id][jumlah]) — bukan [data-qty] yang menempel pada
       tombol - / +.
       Nilai dijumlahkan dengan Number(inp.value) || 0 dan submit
       HANYA diblokir bila total seluruh qty === 0.
       ======================================================= */
    var orderForm = document.getElementById('form-pemesanan');

    if (orderForm) {
        // Hanya SATU listener submit untuk form ini (tanpa onsubmit ganda).
        var qtyInputEls = orderForm.querySelectorAll('input[name*="[jumlah]"]');
        var itemRows = orderForm.querySelectorAll('.qty-row');

        function setRowEnabled(row, enabled) {
            row.querySelectorAll('input').forEach(function (input) {
                input.disabled = !enabled;
            });
        }

        orderForm.addEventListener('submit', function (e) {
            // 1. Jumlahkan seluruh qty di dalam form.
            var totalQty = 0;
            qtyInputEls.forEach(function (inp) {
                var nilai = Number(inp.value) || 0;
                if (nilai < 0) nilai = 0;
                totalQty += nilai;
            });

            // 2. Nonaktifkan baris bernilai 0 (jumlah + hidden
            //    menu_id & harga_satuan) agar server tidak menerima
            //    item setengah jadi, dan bersihkan sisa state lama.
            itemRows.forEach(function (row) {
                var inp = row.querySelector('input[name*="[jumlah]"]');
                var nilai = inp ? Number(inp.value) || 0 : 0;
                if (nilai < 0) nilai = 0;
                setRowEnabled(row, nilai > 0);
            });

            // 3. Blokir HANYA bila total seluruh qty === 0.
            if (totalQty === 0) {
                e.preventDefault();
                itemRows.forEach(function (row) { setRowEnabled(row, true); });
                alert('Pilih minimal satu menu dengan jumlah pesanan lebih dari 0.');
            }
        });
    }
})();
