<x-admin-layout title="Kategori Menu">
    <div
        x-data="{
            createModalOpen: @js(old('_form_mode') === 'create' && $errors->any()),
            editModalOpen: @js(old('_form_mode') === 'edit' && $errors->any()),
            deleteModalOpen: false,
            submitting: false,
            editAction: @js(old('_form_mode') === 'edit' && old('_edit_id') ? route('admin.categories.update', old('_edit_id')) : ''),
            editId: @js(old('_edit_id', '')),
            editName: @js(old('_form_mode') === 'edit' ? old('name', '') : ''),
            deleteAction: '',
            deleteName: '',
            deleteProductCount: 0,
            openCreateModal() {
                this.submitting = false;
                this.createModalOpen = true;
                this.$nextTick(() => this.$refs.createNameInput?.focus());
            },
            openEditModal(id, name, actionUrl) {
                this.submitting = false;
                this.editId = id;
                this.editName = name;
                this.editAction = actionUrl;
                this.editModalOpen = true;
                this.$nextTick(() => this.$refs.editNameInput?.focus());
            },
            openDeleteModal(name, productCount, actionUrl) {
                this.submitting = false;
                this.deleteName = name;
                this.deleteProductCount = productCount;
                this.deleteAction = actionUrl;
                this.deleteModalOpen = true;
                this.$nextTick(() => this.$refs.cancelDeleteBtn?.focus());
            },
            closeAllModals() {
                this.createModalOpen = false;
                this.editModalOpen = false;
                this.deleteModalOpen = false;
                this.submitting = false;
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

        {{-- Page Action Bar --}}
        <div class="flex flex-col gap-3 rounded-lg border border-[#E2DDD5] bg-white p-4 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <div>
                <h2 class="text-base font-semibold text-[#1C1917]">Daftar Kategori</h2>
                <p class="mt-0.5 text-sm text-[#57534E]">
                    Total <span class="font-semibold text-[#1C1917] tabular-nums">{{ $categories->count() }}</span> kategori menu terdaftar.
                </p>
            </div>
            <button
                type="button"
                @click="openCreateModal()"
                class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
            >
                Tambah Kategori
            </button>
        </div>

        {{-- Categories List / Empty State --}}
        @if ($categories->isEmpty())
            <div class="rounded-lg border border-[#E2DDD5] bg-white px-6 py-12 text-center" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                <h3 class="text-base font-semibold text-[#1C1917]">Belum ada kategori menu</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-[#57534E]">
                    Tambahkan kategori seperti Coffee, Non-Coffee, atau Pastry untuk mengelompokkan menu di kasir.
                </p>
                <div class="mt-5">
                    <button
                        type="button"
                        @click="openCreateModal()"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Tambah Kategori Pertama
                    </button>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-[#E2DDD5] bg-white" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                {{-- Desktop & Tablet Table --}}
                <div class="hidden sm:block">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-[#E2DDD5] bg-[#F7F5F0]">
                                <th scope="col" class="w-16 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#57534E]">No</th>
                                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[#57534E]">Nama Kategori</th>
                                <th scope="col" class="w-40 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[#57534E]">Jumlah Menu</th>
                                <th scope="col" class="w-48 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[#57534E]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2DDD5]">
                            @foreach ($categories as $category)
                                <tr class="transition-colors hover:bg-[#F7F5F0]/60">
                                    <td class="px-4 py-3 text-sm font-medium text-[#57534E] tabular-nums">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-[#1C1917]">
                                        {{ $category->name }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-[#1C1917] tabular-nums">
                                        {{ $category->products_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="openEditModal({{ $category->id }}, @js($category->name), @js(route('admin.categories.update', $category)))"
                                                class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                @click="openDeleteModal(@js($category->name), {{ $category->products_count }}, @js(route('admin.categories.destroy', $category)))"
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

                {{-- Mobile Stacked List (prevents horizontal overflow) --}}
                <ul class="divide-y divide-[#E2DDD5] sm:hidden">
                    @foreach ($categories as $category)
                        <li class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-[#57534E] tabular-nums">
                                        #{{ $loop->iteration }}
                                    </p>
                                    <p class="mt-0.5 truncate text-base font-semibold text-[#1C1917]">
                                        {{ $category->name }}
                                    </p>
                                    <p class="mt-1 text-sm text-[#57534E]">
                                        Jumlah menu: <span class="font-semibold text-[#1C1917] tabular-nums">{{ $category->products_count }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openEditModal({{ $category->id }}, @js($category->name), @js(route('admin.categories.update', $category)))"
                                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    @click="openDeleteModal(@js($category->name), {{ $category->products_count }}, @js(route('admin.categories.destroy', $category)))"
                                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-md border border-[#B91C1C]/30 bg-white px-3 py-2 text-sm font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2"
                                >
                                    Hapus
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Modal: Tambah Kategori --}}
        <div
            x-show="createModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-category-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="create-category-title" class="text-base font-semibold text-[#1C1917]">
                        Tambah Kategori
                    </h3>
                    <button
                        type="button"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal tambah kategori"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.categories.store') }}"
                    @submit="submitting = true"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="_form_mode" value="create">

                    <div>
                        <label for="create-category-name" class="block text-sm font-semibold text-[#1C1917]">
                            Nama Kategori
                        </label>
                        <input
                            id="create-category-name"
                            x-ref="createNameInput"
                            type="text"
                            name="name"
                            value="{{ old('_form_mode') === 'create' ? old('name') : '' }}"
                            maxlength="100"
                            required
                            placeholder="Contoh: Coffee"
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'create' && $errors->has('name'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('name') }}</p>
                        @endif
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
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Kategori'">Simpan Kategori</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Edit Kategori --}}
        <div
            x-show="editModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-category-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="edit-category-title" class="text-base font-semibold text-[#1C1917]">
                        Edit Kategori
                    </h3>
                    <button
                        type="button"
                        @click="closeAllModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal edit kategori"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    :action="editAction"
                    @submit="submitting = true"
                    class="mt-4 space-y-4"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form_mode" value="edit">
                    <input type="hidden" name="_edit_id" :value="editId">

                    <div>
                        <label for="edit-category-name" class="block text-sm font-semibold text-[#1C1917]">
                            Nama Kategori
                        </label>
                        <input
                            id="edit-category-name"
                            x-ref="editNameInput"
                            type="text"
                            name="name"
                            x-model="editName"
                            maxlength="100"
                            required
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/60 focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        @if (old('_form_mode') === 'edit' && $errors->has('name'))
                            <p class="mt-1.5 text-sm font-medium text-[#B91C1C]">{{ $errors->first('name') }}</p>
                        @endif
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

        {{-- Modal: Konfirmasi Hapus Kategori --}}
        <div
            x-show="deleteModalOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-category-title"
        >
            <div
                @click.outside="closeAllModals()"
                class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h3 id="delete-category-title" class="text-base font-semibold text-[#1C1917]">
                        Hapus Kategori
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

                <div class="mt-4 space-y-3">
                    <p class="text-sm text-[#1C1917]">
                        Yakin ingin menghapus kategori <span class="font-semibold" x-text="deleteName"></span>?
                    </p>
                    <p
                        x-show="deleteProductCount > 0"
                        class="rounded-md border border-[#B45309]/30 bg-[#FEF3C7] px-3 py-2 text-sm font-medium text-[#B45309]"
                    >
                        Kategori ini memiliki <span class="font-bold tabular-nums" x-text="deleteProductCount"></span> menu yang juga akan ikut terhapus.
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
                        <span x-text="submitting ? 'Menghapus...' : 'Hapus Kategori'">Hapus Kategori</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
