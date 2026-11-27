<x-admin-layout title="Menu/Produk">
    <div
        x-data="{
            createModalOpen: @js(old('_form_mode') === 'create' && $errors->any()),
            editModalOpen: @js(old('_form_mode') === 'edit' && $errors->any()),
            deleteModalOpen: false,
            submitting: false,
            togglingIds: {},
            availabilityMap: @js($products->mapWithKeys(fn ($product) => [$product->id => (bool) $product->is_available])),
            toastMessage: '',
            toastType: 'success',
            editAction: @js(old('_form_mode') === 'edit' && old('_edit_id') ? route('admin.products.update', old('_edit_id')) : ''),
            editId: @js(old('_edit_id', '')),
            editCategoryId: @js(old('_form_mode') === 'edit' ? old('category_id', '') : ''),
            editName: @js(old('_form_mode') === 'edit' ? old('name', '') : ''),
            editPrice: @js(old('_form_mode') === 'edit' ? old('price', '') : ''),
            editIsAvailable: @js(old('_form_mode') === 'edit' ? (bool) old('is_available', false) : true),
            editImageUrl: @js(old('_form_mode') === 'edit' ? old('_edit_image_url', '') : ''),
            editRemoveImage: @js(old('_form_mode') === 'edit' ? (bool) old('remove_image', false) : false),
            deleteAction: '',
            deleteName: '',
            filterSearch: @js($filters['search']),
            filterCategory: @js($filters['category_id']),
            filterAvailability: @js($filters['availability']),
            initialSearch: @js($filters['search']),
            initialCategory: @js($filters['category_id']),
            initialAvailability: @js($filters['availability']),
            isFiltering: false,
            get hasActiveFilter() {
                return this.filterSearch.trim() !== '' || this.filterCategory !== '' || this.filterAvailability !== '';
            },
            init() {
                if (sessionStorage.getItem('admin_products_refocus_search') === '1') {
                    sessionStorage.removeItem('admin_products_refocus_search');
                    this.$nextTick(() => {
                        const input = this.$refs.filterSearchInput;
                        if (input) {
                            input.focus();
                            const len = input.value.length;
                            input.setSelectionRange(len, len);
                        }
                    });
                }
            },
            submitFilter(fromSearch = false) {
                const nextSearch = this.filterSearch.trim();
                if (
                    nextSearch === this.initialSearch &&
                    this.filterCategory === this.initialCategory &&
                    this.filterAvailability === this.initialAvailability
                ) {
                    return;
                }

                this.isFiltering = true;
                if (fromSearch) {
                    sessionStorage.setItem('admin_products_refocus_search', '1');
                }
                this.$nextTick(() => this.$refs.filterForm?.submit());
            },
            openCreateModal() {
                this.submitting = false;
                this.createModalOpen = true;
                this.$nextTick(() => this.$refs.createCategoryInput?.focus());
            },
            openEditModal(product) {
                this.submitting = false;
                this.editId = product.id;
                this.editCategoryId = String(product.category_id);
                this.editName = product.name;
                this.editPrice = product.price;
                this.editIsAvailable = Boolean(this.availabilityMap[product.id] ?? product.is_available);
                this.editImageUrl = product.image_url || '';
                this.editRemoveImage = false;
                this.editAction = product.update_url;
                this.editModalOpen = true;
                this.$nextTick(() => this.$refs.editNameInput?.focus());
            },
            openDeleteModal(name, actionUrl) {
                this.submitting = false;
                this.deleteName = name;
                this.deleteAction = actionUrl;
                this.deleteModalOpen = true;
                this.$nextTick(() => this.$refs.cancelDeleteBtn?.focus());
            },
            closeAllModals() {
                this.createModalOpen = false;
                this.editModalOpen = false;
                this.deleteModalOpen = false;
                this.submitting = false;
            },
            async toggleAvailability(id, url) {
                if (this.togglingIds[id]) return;
                this.togglingIds[id] = true;
                this.toastMessage = '';

                try {
                    const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memperbarui status menu.');
                    }

                    const data = await response.json();
                    this.availabilityMap[id] = Boolean(data.is_available);
                    this.toastType = 'success';
                    this.toastMessage = data.message;
                } catch (error) {
                    this.toastType = 'error';
                    this.toastMessage = 'Gagal mengubah status ketersediaan. Muat ulang halaman lalu coba lagi.';
                } finally {
                    this.togglingIds[id] = false;
                }
            }
        }"
        @keydown.escape.window="closeAllModals()"
        class="space-y-5"
    >
        {{-- Status & Error Feedback --}}
        @if (session('status'))
            <div
                role="status"
                class="flex items-center justify-between gap-3 rounded-lg border border-[#15803D]/25 bg-[#DCFCE7] px-4 py-3 text-sm font-medium text-[#15803D]"
            >
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div
                role="alert"
                class="flex items-center justify-between gap-3 rounded-lg border border-[#B91C1C]/25 bg-[#FEE2E2] px-4 py-3 text-sm font-medium text-[#B91C1C]"
            >
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div
            x-show="toastMessage !== ''"
            x-transition.opacity.duration.150ms
            style="display: none;"
            :role="toastType === 'error' ? 'alert' : 'status'"
            :class="toastType === 'error'
                ? 'border-[#B91C1C]/25 bg-[#FEE2E2] text-[#B91C1C]'
                : 'border-[#15803D]/25 bg-[#DCFCE7] text-[#15803D]'"
            class="flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm font-medium"
        >
            <span x-text="toastMessage"></span>
            <button
                type="button"
                @click="toastMessage = ''"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md transition-colors hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                aria-label="Tutup notifikasi"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                </svg>
            </button>
        </div>

        {{-- Page Action Bar --}}
        <div class="flex flex-col gap-3 rounded-lg border border-[#E2DDD5] bg-white p-4 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <div>
                <h2 class="text-base font-semibold text-[#1C1917]">Daftar Menu</h2>
                <p class="mt-0.5 text-sm text-[#57534E]">
                    Menampilkan <span class="font-semibold text-[#1C1917] tabular-nums">{{ $products->count() }}</span> dari <span class="font-semibold text-[#1C1917] tabular-nums">{{ $totalProducts }}</span> menu terdaftar.
                </p>
            </div>
            @if ($categories->isEmpty())
                <a
                    href="{{ route('admin.categories.index') }}"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-[#EFECE6] px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#E2DDD5] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                >
                    Buat Kategori Dulu
                </a>
            @else
                <button
                    type="button"
                    @click="openCreateModal()"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                >
                    Tambah Menu
                </button>
            @endif
        </div>

        {{-- Search & Filter Bar --}}
        @if ($totalProducts > 0)
            <form
                x-ref="filterForm"
                method="GET"
                action="{{ route('admin.products.index') }}"
                class="grid grid-cols-1 gap-3 rounded-lg border border-[#E2DDD5] bg-white p-4 sm:grid-cols-2 lg:flex lg:flex-row lg:items-end"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="sm:col-span-2 lg:flex-1">
                    <label for="filter-search" class="block text-xs font-semibold uppercase tracking-wider text-[#57534E]">
                        Cari Menu
                    </label>
                    <input
                        id="filter-search"
                        x-ref="filterSearchInput"
                        x-model="filterSearch"
                        @input.debounce.400ms="submitFilter(true)"
                        @keydown.enter.prevent="submitFilter(true)"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Ketik nama menu..."
                        class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                    >
                </div>

                <div class="lg:w-56">
                    <label for="filter-category" class="block text-xs font-semibold uppercase tracking-wider text-[#57534E]">
                        Kategori
                    </label>
                    <select
                        id="filter-category"
                        x-model="filterCategory"
                        @change="submitFilter()"
                        name="category_id"
                        class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:w-44">
                    <label for="filter-availability" class="block text-xs font-semibold uppercase tracking-wider text-[#57534E]">
                        Status
                    </label>
                    <select
                        id="filter-availability"
                        x-model="filterAvailability"
                        @change="submitFilter()"
                        name="availability"
                        class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                    >
                        <option value="">Semua Status</option>
                        <option value="available" @selected($filters['availability'] === 'available')>Tersedia</option>
                        <option value="unavailable" @selected($filters['availability'] === 'unavailable')>Stok Habis</option>
                    </select>
                </div>

                <div
                    x-show="isFiltering || hasActiveFilter"
                    @if (! ($filters['search'] !== '' || $filters['category_id'] !== '' || $filters['availability'] !== '')) style="display: none;" @endif
                    class="flex items-center gap-2 sm:col-span-2 lg:w-auto lg:shrink-0"
                >
                    <span
                        x-show="isFiltering"
                        style="display: none;"
                        role="status"
                        class="text-xs font-medium text-[#57534E]"
                    >
                        Memfilter...
                    </span>
                    <a
                        x-show="hasActiveFilter"
                        @if (! ($filters['search'] !== '' || $filters['category_id'] !== '' || $filters['availability'] !== '')) style="display: none;" @endif
                        href="{{ route('admin.products.index') }}"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-3.5 py-2 text-sm font-semibold text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Reset
                    </a>
                </div>
            </form>
        @endif

        {{-- Products List / Empty States --}}
        @if ($totalProducts === 0)
            <div class="rounded-lg border border-[#E2DDD5] bg-white px-6 py-12 text-center" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                <h3 class="text-base font-semibold text-[#1C1917]">Belum ada menu terdaftar</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-[#57534E]">
                    @if ($categories->isEmpty())
                        Tambahkan minimal satu kategori menu terlebih dahulu sebelum menambahkan produk.
                    @else
                        Tambahkan menu minuman atau makanan beserta harga dan fotonya agar siap dipilih kasir.
                    @endif
                </p>
                <div class="mt-5">
                    @if ($categories->isEmpty())
                        <a
                            href="{{ route('admin.categories.index') }}"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Kelola Kategori
                        </a>
                    @else
                        <button
                            type="button"
                            @click="openCreateModal()"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Tambah Menu Pertama
                        </button>
                    @endif
                </div>
            </div>
        @elseif ($products->isEmpty())
            <div class="rounded-lg border border-[#E2DDD5] bg-white px-6 py-12 text-center" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                <h3 class="text-base font-semibold text-[#1C1917]">Menu tidak ditemukan</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-[#57534E]">
                    Tidak ada menu yang cocok dengan pencarian atau filter yang dipilih.
                </p>
                <div class="mt-5">
                    <a
                        href="{{ route('admin.products.index') }}"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Tampilkan Semua Menu
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-[#E2DDD5] bg-white" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                {{-- Desktop & Tablet Table --}}
                <div class="hidden md:block">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-[#E2DDD5] bg-[#F7F5F0]">
                                <th scope="col" class="w-14 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#57534E]">No</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#57534E]">Menu</th>
                                <th scope="col" class="w-40 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#57534E]">Kategori</th>
                                <th scope="col" class="w-36 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[#57534E]">Harga</th>
                                <th scope="col" class="w-40 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[#57534E]">Status</th>
                                <th scope="col" class="w-44 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[#57534E]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2DDD5]">
                            @foreach ($products as $product)
                                @php
                                    $imageUrl = $product->image ? asset('storage/' . $product->image) : '';
                                @endphp
                                <tr class="transition-colors hover:bg-[#F7F5F0]/60">
                                    <td class="px-4 py-3 text-sm font-medium text-[#57534E] tabular-nums">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($product->image)
                                                <img
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $product->name }}"
                                                    width="48"
                                                    height="48"
                                                    class="h-12 w-12 shrink-0 rounded-md border border-[#E2DDD5] object-cover"
                                                >
                                            @else
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0] text-[#57534E]">
                                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1.5a2.5 2.5 0 0 1 0 5H17M5 8h12v6a5 5 0 0 1-5 5h-2a5 5 0 0 1-5-5V8Z" />
                                                        <path stroke-linecap="round" d="M4 21h14" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <span class="text-sm font-semibold text-[#1C1917]">
                                                {{ $product->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-[#57534E]">
                                        {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-[#1C1917] tabular-nums">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            @click="toggleAvailability({{ $product->id }}, @js(route('admin.products.toggle-availability', $product)))"
                                            :disabled="Boolean(togglingIds[{{ $product->id }}])"
                                            :aria-pressed="Boolean(availabilityMap[{{ $product->id }}]).toString()"
                                            :class="availabilityMap[{{ $product->id }}]
                                                ? 'border-[#15803D]/25 bg-[#DCFCE7] text-[#15803D] hover:bg-[#DCFCE7]/80'
                                                : 'border-[#B91C1C]/25 bg-[#FEE2E2] text-[#B91C1C] hover:bg-[#FEE2E2]/80'"
                                            class="inline-flex min-h-[44px] min-w-[116px] items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                                        >
                                            <span x-text="togglingIds[{{ $product->id }}] ? 'Menyimpan...' : (availabilityMap[{{ $product->id }}] ? 'Tersedia' : 'Stok Habis')">
                                                {{ $product->is_available ? 'Tersedia' : 'Stok Habis' }}
                                            </span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="openEditModal({
                                                    id: {{ $product->id }},
                                                    category_id: {{ $product->category_id }},
                                                    name: @js($product->name),
                                                    price: {{ $product->price }},
                                                    is_available: {{ $product->is_available ? 'true' : 'false' }},
                                                    image_url: @js($imageUrl),
                                                    update_url: @js(route('admin.products.update', $product))
                                                })"
                                                class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                @click="openDeleteModal(@js($product->name), @js(route('admin.products.destroy', $product)))"
                                                class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#B91C1C]/30 bg-white px-3 py-2 text-sm font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Stacked List --}}
                <ul class="divide-y divide-[#E2DDD5] md:hidden">
                    @foreach ($products as $product)
                        @php
                            $imageUrl = $product->image ? asset('storage/' . $product->image) : '';
                        @endphp
                        <li class="p-4">
                            <div class="flex items-start gap-3">
                                @if ($product->image)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $product->name }}"
                                        width="56"
                                        height="56"
                                        class="h-14 w-14 shrink-0 rounded-md border border-[#E2DDD5] object-cover"
                                    >
                                @else
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0] text-[#57534E]">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1.5a2.5 2.5 0 0 1 0 5H17M5 8h12v6a5 5 0 0 1-5 5h-2a5 5 0 0 1-5-5V8Z" />
                                            <path stroke-linecap="round" d="M4 21h14" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-[#57534E]">
                                        {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                    </p>
                                    <p class="mt-0.5 truncate text-base font-semibold text-[#1C1917]">
                                        {{ $product->name }}
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-[#1C1917] tabular-nums">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    @click="toggleAvailability({{ $product->id }}, @js(route('admin.products.toggle-availability', $product)))"
                                    :disabled="Boolean(togglingIds[{{ $product->id }}])"
                                    :aria-pressed="Boolean(availabilityMap[{{ $product->id }}]).toString()"
                                    :class="availabilityMap[{{ $product->id }}]
                                        ? 'border-[#15803D]/25 bg-[#DCFCE7] text-[#15803D] hover:bg-[#DCFCE7]/80'
                                        : 'border-[#B91C1C]/25 bg-[#FEE2E2] text-[#B91C1C] hover:bg-[#FEE2E2]/80'"
                                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                                >
                                    <span x-text="togglingIds[{{ $product->id }}] ? 'Menyimpan...' : (availabilityMap[{{ $product->id }}] ? 'Tersedia' : 'Stok Habis')">
                                        {{ $product->is_available ? 'Tersedia' : 'Stok Habis' }}
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    @click="openEditModal({
                                        id: {{ $product->id }},
                                        category_id: {{ $product->category_id }},
                                        name: @js($product->name),
                                        price: {{ $product->price }},
                                        is_available: {{ $product->is_available ? 'true' : 'false' }},
                                        image_url: @js($imageUrl),
                                        update_url: @js(route('admin.products.update', $product))
                                    })"
                                    class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    @click="openDeleteModal(@js($product->name), @js(route('admin.products.destroy', $product)))"
                                    class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#B91C1C]/30 bg-white px-4 py-2 text-sm font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2"
                                >
                                    Hapus
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Modal: Tambah Menu --}}
        <div
            x-show="createModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-product-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-lg rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="create-product-title" class="text-base font-semibold text-[#1C1917]">
                        Tambah Menu
                    </h3>
                    <button
                        type="button"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal tambah menu"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.products.store') }}"
                    enctype="multipart/form-data"
                    @submit="submitting = true"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_form_mode" value="create">

                    <div>
                        <label for="create-product-category" class="block text-sm font-semibold text-[#1C1917]">
                            Kategori
                        </label>
                        <select
                            id="create-product-category"
                            x-ref="createCategoryInput"
                            name="category_id"
                            required
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('_form_mode') === 'create' && (string) old('category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if (old('_form_mode') === 'create' && $errors->has('category_id'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('category_id') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="create-product-name" class="block text-sm font-semibold text-[#1C1917]">
                            Nama Menu
                        </label>
                        <input
                            id="create-product-name"
                            type="text"
                            name="name"
                            value="{{ old('_form_mode') === 'create' ? old('name') : '' }}"
                            maxlength="150"
                            required
                            placeholder="Contoh: Es Kopi Susu Gula Aren"
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'create' && $errors->has('name'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="create-product-price" class="block text-sm font-semibold text-[#1C1917]">
                            Harga (Rupiah)
                        </label>
                        <input
                            id="create-product-price"
                            type="number"
                            name="price"
                            value="{{ old('_form_mode') === 'create' ? old('price') : '' }}"
                            min="0"
                            step="100"
                            required
                            placeholder="28000"
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 tabular-nums focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'create' && $errors->has('price'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('price') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="create-product-image" class="block text-sm font-semibold text-[#1C1917]">
                            Foto Menu (Opsional)
                        </label>
                        <input
                            id="create-product-image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="mt-1.5 block w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#57534E] file:mr-3 file:rounded-md file:border-0 file:bg-[#EFECE6] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#1C1917] hover:file:bg-[#E2DDD5] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        <p class="mt-1 text-xs text-[#57534E]">Format JPG, PNG, atau WEBP maksimal 2 MB.</p>
                        @if (old('_form_mode') === 'create' && $errors->has('image'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('image') }}</p>
                        @endif
                    </div>

                    <div class="pt-1">
                        <label for="create-product-available" class="inline-flex min-h-[44px] items-center gap-2.5 text-sm font-medium text-[#1C1917]">
                            <input
                                id="create-product-available"
                                type="checkbox"
                                name="is_available"
                                value="1"
                                @checked(old('_form_mode') === 'create' ? old('is_available') : true)
                                class="h-4 w-4 rounded border-[#E2DDD5] text-[#9A3412] focus:ring-[#9A3412]"
                            >
                            <span>Menu langsung tersedia di kasir</span>
                        </label>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="closeAllModals()"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                        >
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Menu'">Simpan Menu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Edit Menu --}}
        <div
            x-show="editModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-product-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-lg rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="edit-product-title" class="text-base font-semibold text-[#1C1917]">
                        Edit Menu
                    </h3>
                    <button
                        type="button"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal edit menu"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    :action="editAction"
                    enctype="multipart/form-data"
                    @submit="submitting = true"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form_mode" value="edit">
                    <input type="hidden" name="_edit_id" :value="editId">
                    <input type="hidden" name="_edit_image_url" :value="editImageUrl">

                    <div>
                        <label for="edit-product-category" class="block text-sm font-semibold text-[#1C1917]">
                            Kategori
                        </label>
                        <select
                            id="edit-product-category"
                            name="category_id"
                            x-model="editCategoryId"
                            required
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @if (old('_form_mode') === 'edit' && $errors->has('category_id'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('category_id') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="edit-product-name" class="block text-sm font-semibold text-[#1C1917]">
                            Nama Menu
                        </label>
                        <input
                            id="edit-product-name"
                            x-ref="editNameInput"
                            type="text"
                            name="name"
                            x-model="editName"
                            maxlength="150"
                            required
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'edit' && $errors->has('name'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="edit-product-price" class="block text-sm font-semibold text-[#1C1917]">
                            Harga (Rupiah)
                        </label>
                        <input
                            id="edit-product-price"
                            type="number"
                            name="price"
                            x-model="editPrice"
                            min="0"
                            step="100"
                            required
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] tabular-nums focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'edit' && $errors->has('price'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('price') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="edit-product-image" class="block text-sm font-semibold text-[#1C1917]">
                            Ganti Foto Menu (Opsional)
                        </label>
                        <div x-show="editImageUrl !== ''" class="mt-2 flex items-center gap-3 rounded-md border border-[#E2DDD5] bg-[#F7F5F0] p-2.5">
                            <img
                                :src="editImageUrl"
                                :alt="editName || 'Foto menu saat ini'"
                                width="48"
                                height="48"
                                class="h-12 w-12 shrink-0 rounded-md border border-[#E2DDD5] object-cover"
                            >
                            <label for="edit-remove-image" class="inline-flex min-h-[44px] items-center gap-2 text-sm font-medium text-[#B91C1C]">
                                <input
                                    id="edit-remove-image"
                                    type="checkbox"
                                    name="remove_image"
                                    value="1"
                                    x-model="editRemoveImage"
                                    class="h-4 w-4 rounded border-[#E2DDD5] text-[#B91C1C] focus:ring-[#B91C1C]"
                                >
                                <span>Hapus foto saat ini</span>
                            </label>
                        </div>
                        <input
                            id="edit-product-image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="mt-2 block w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#57534E] file:mr-3 file:rounded-md file:border-0 file:bg-[#EFECE6] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#1C1917] hover:file:bg-[#E2DDD5] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        <p class="mt-1 text-xs text-[#57534E]">Kosongkan jika tidak ingin mengganti foto. Maksimal 2 MB.</p>
                        @if (old('_form_mode') === 'edit' && $errors->has('image'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('image') }}</p>
                        @endif
                    </div>

                    <div class="pt-1">
                        <label for="edit-product-available" class="inline-flex min-h-[44px] items-center gap-2.5 text-sm font-medium text-[#1C1917]">
                            <input
                                id="edit-product-available"
                                type="checkbox"
                                name="is_available"
                                value="1"
                                x-model="editIsAvailable"
                                class="h-4 w-4 rounded border-[#E2DDD5] text-[#9A3412] focus:ring-[#9A3412]"
                            >
                            <span>Menu tersedia di kasir</span>
                        </label>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="closeAllModals()"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                        >
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Konfirmasi Hapus Menu --}}
        <div
            x-show="deleteModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-product-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="delete-product-title" class="text-base font-semibold text-[#1C1917]">
                        Hapus Menu
                    </h3>
                    <button
                        type="button"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal konfirmasi hapus"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-[#1C1917]">
                        Yakin ingin menghapus menu <span class="font-semibold" x-text="deleteName"></span>? Foto produk yang tersimpan juga akan dihapus.
                    </p>
                </div>

                <form
                    method="POST"
                    :action="deleteAction"
                    @submit="submitting = true"
                    class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        x-ref="cancelDeleteBtn"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#B91C1C] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#991B1B] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2 disabled:opacity-60"
                    >
                        <span x-text="submitting ? 'Menghapus...' : 'Hapus Menu'">Hapus Menu</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
