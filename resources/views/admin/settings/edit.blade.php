<x-admin-layout title="Pengaturan Kafe">
    <div
        x-data="{
            cafeName: @js(old('cafe_name', $setting->cafe_name ?? '')),
            address: @js(old('address', $setting->address ?? '')),
            phone: @js(old('phone', $setting->phone ?? '')),
            submitting: false,
            resetForm() {
                this.cafeName = @js($setting->cafe_name ?? '');
                this.address = @js($setting->address ?? '');
                this.phone = @js($setting->phone ?? '');
            }
        }"
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

        @if ($errors->any())
            <div
                role="alert"
                class="rounded-lg border border-[#B91C1C]/25 bg-[#FEE2E2] px-4 py-3 text-sm text-[#B91C1C]"
            >
                <p class="font-semibold">Periksa kembali data yang diisi:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Split Layout: Form Pengaturan (Kiri) + Pratinjau Struk & Navbar (Kanan) --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            {{-- Form Identitas Kafe --}}
            <section
                aria-labelledby="settings-form-heading"
                class="rounded-lg border border-[#E2DDD5] bg-white p-5 sm:p-6 lg:col-span-7"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="border-b border-[#E2DDD5] pb-4">
                    <h2 id="settings-form-heading" class="text-base font-semibold text-[#1C1917]">
                        Identitas &amp; Informasi Kafe
                    </h2>
                    <p class="mt-1 text-sm text-[#57534E]">
                        Perubahan pada data ini langsung diterapkan pada judul halaman, sidebar, navbar kasir, dan header struk belanja.
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.settings.update') }}"
                    @submit="submitting = true"
                    class="mt-5 space-y-5"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="cafe_name" class="block text-sm font-semibold text-[#1C1917]">
                            Nama Kafe <span class="text-[#B91C1C]" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="cafe_name"
                            name="cafe_name"
                            type="text"
                            x-model="cafeName"
                            maxlength="100"
                            required
                            autocomplete="organization"
                            placeholder="Contoh: Kedai Kopi Senja"
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] placeholder-[#57534E]/70 transition-colors focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        <p class="mt-1 text-xs text-[#57534E]">
                            Ditampilkan di judul browser, sidebar Admin, navbar Kasir, dan bagian paling atas struk.
                        </p>
                        @error('cafe_name')
                            <p class="mt-1 text-xs font-medium text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-semibold text-[#1C1917]">
                            Alamat Kafe
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            x-model="address"
                            maxlength="500"
                            autocomplete="street-address"
                            placeholder="Contoh: Jl. Kopi No. 12, Jakarta Selatan"
                            class="mt-1.5 block w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2.5 text-sm text-[#1C1917] placeholder-[#57534E]/70 transition-colors focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        ></textarea>
                        <p class="mt-1 text-xs text-[#57534E]">
                            Dicetak di bawah nama kafe pada struk transaksi. Boleh dikosongkan jika tidak perlu dicetak.
                        </p>
                        @error('address')
                            <p class="mt-1 text-xs font-medium text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#1C1917]">
                            Nomor Telepon
                        </label>
                        <input
                            id="phone"
                            name="phone"
                            type="text"
                            x-model="phone"
                            maxlength="30"
                            autocomplete="tel"
                            placeholder="Contoh: 021-5550123"
                            class="mt-1.5 block min-h-[44px] w-full rounded-md border border-[#E2DDD5] bg-white px-3 py-2 text-sm text-[#1C1917] tabular-nums placeholder-[#57534E]/70 transition-colors focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                        >
                        <p class="mt-1 text-xs text-[#57534E]">
                            Nomor kontak kafe yang tampil di struk untuk keperluan pelanggan.
                        </p>
                        @error('phone')
                            <p class="mt-1 text-xs font-medium text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-[#E2DDD5] pt-4 sm:flex-row sm:items-center sm:justify-end">
                        <button
                            type="button"
                            @click="resetForm()"
                            :disabled="submitting"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                        >
                            Kembalikan Semula
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#9A3412] px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60"
                        >
                            <span x-show="!submitting">Simpan Pengaturan</span>
                            <span x-show="submitting" style="display: none;">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>

            {{-- Panel Pratinjau Langsung --}}
            <aside
                aria-labelledby="preview-heading"
                class="space-y-4 lg:col-span-5"
            >
                <div
                    class="rounded-lg border border-[#E2DDD5] bg-white p-5 sm:p-6"
                    style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
                >
                    <div class="border-b border-[#E2DDD5] pb-3">
                        <h2 id="preview-heading" class="text-base font-semibold text-[#1C1917]">
                            Pratinjau Tampilan
                        </h2>
                        <p class="mt-0.5 text-xs text-[#57534E]">
                            Simulasi langsung nama pada navbar dan header struk saat ini.
                        </p>
                    </div>

                    {{-- Simulasi Navbar --}}
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">
                            Navbar &amp; Sidebar
                        </p>
                        <div class="mt-2 rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-3.5 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#57534E]">Panel Admin</p>
                            <p
                                class="truncate text-base font-bold text-[#1C1917]"
                                x-text="cafeName.trim() || 'Nama Kafe'"
                            >{{ $setting->cafe_name ?: 'Nama Kafe' }}</p>
                        </div>
                    </div>

                    {{-- Simulasi Header Struk --}}
                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#57534E]">
                            Header Struk Pelanggan
                        </p>
                        <div class="mt-2 rounded-md border border-dashed border-[#E2DDD5] bg-[#F7F5F0] p-4 text-center">
                            <p
                                class="text-base font-bold text-[#1C1917]"
                                x-text="cafeName.trim() || 'Nama Kafe'"
                            >{{ $setting->cafe_name ?: 'Nama Kafe' }}</p>
                            <p
                                class="mt-1 whitespace-pre-line text-xs text-[#57534E]"
                                x-show="address.trim().length > 0"
                                x-text="address.trim()"
                            >{{ $setting->address }}</p>
                            <p
                                class="mt-0.5 text-xs text-[#57534E] tabular-nums"
                                x-show="phone.trim().length > 0"
                                x-text="'Telp: ' + phone.trim()"
                            >{{ $setting->phone ? 'Telp: ' . $setting->phone : '' }}</p>

                            <div class="my-3 border-t border-dashed border-[#E2DDD5]"></div>

                            <div class="space-y-1 text-left text-xs text-[#57534E] tabular-nums">
                                <div class="flex justify-between">
                                    <span>Contoh Item (1x)</span>
                                    <span class="font-semibold text-[#1C1917]">Rp 25.000</span>
                                </div>
                                <div class="flex justify-between border-t border-dashed border-[#E2DDD5] pt-1 font-bold text-[#1C1917]">
                                    <span>Total</span>
                                    <span>Rp 25.000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-admin-layout>
