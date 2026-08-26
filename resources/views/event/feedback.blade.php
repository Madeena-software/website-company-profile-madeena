@extends('layouts.app')

@section('title', 'Feedback — ' . $event->name)
@section('description', 'Sampaikan kesan dan pesan Anda untuk ' . $event->name . ' melalui formulir mobile-friendly Madeena.')

@section('content')
    <section
        class="bg-gradient-to-br from-madeena-blue via-slate-950 to-madeena-teal pt-24 pb-14 text-slate-900 sm:pt-28 sm:pb-20">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:gap-8 lg:px-8">
            <aside class="overflow-hidden rounded-lg bg-madeena-blue text-white shadow-xl shadow-slate-900/10">
                <div class="p-5 sm:p-8 lg:p-10">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-current.png') }}" alt="Logo Madeena"
                            class="h-11 w-auto rounded bg-white p-1.5 sm:h-12">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-madeena-teal">Madeena</p>
                            <p class="text-sm font-semibold text-white/85">{{ $event->name }}</p>
                        </div>
                    </div>

                    <div class="mt-8 max-w-xl space-y-4">
                        <p
                            class="inline-flex rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-white/90">
                            {{ $event->name }}
                        </p>
                        <h1 class="text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                            Kesan dan Pesan untuk {{ $event->name }}
                        </h1>
                        <p class="text-base leading-7 text-white/75 sm:text-lg">
                            Masukan Anda membantu kami menyempurnakan pengalaman, produk, dan layanan Madeena untuk
                            kolaborasi berikutnya.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <p class="text-sm font-bold uppercase tracking-wide text-madeena-teal">01</p>
                            <p class="mt-2 text-sm leading-6 text-white/80">Isi identitas singkat agar tim kami dapat
                                menindaklanjuti dengan tepat.</p>
                        </div>
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <p class="text-sm font-bold uppercase tracking-wide text-madeena-teal">02</p>
                            <p class="mt-2 text-sm leading-6 text-white/80">Tulis pengalaman, saran, atau kebutuhan yang
                                ingin didiskusikan.</p>
                        </div>
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4">
                            <p class="text-sm font-bold uppercase tracking-wide text-madeena-teal">03</p>
                            <p class="mt-2 text-sm leading-6 text-white/80">Feedback terkirim langsung ke dashboard tim
                                Madeena.</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/15 bg-slate-900/25 px-5 py-5 sm:px-8 lg:px-10">
                    <p class="text-sm font-semibold text-white">PT Madeena Karya Indonesia</p>
                    <p class="mt-1 text-sm leading-6 text-white/70">Produsen Digital Radiography buatan Indonesia.</p>
                </div>
            </aside>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-xl shadow-slate-900/10 sm:p-6 lg:p-8">
                <div class="mb-6 border-b border-slate-200 pb-5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-madeena-teal">Form Feedback</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Bagikan kesan dan pesan Anda</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Tips: gunakan input suara untuk memudahkan dalam pengisian.
                    </p>
                </div>

                @if (session('success'))
                    <div
                        class="mb-6 flex gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-800"
                        role="status" aria-live="polite">
                        <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-6 flex gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium leading-6 text-rose-700"
                        role="alert">
                        <i class="fas fa-circle-exclamation mt-0.5 text-rose-600" aria-hidden="true"></i>
                        <span>{{ $errors->first('_token') ?: 'Mohon periksa kembali isian Anda sebelum mengirimkan formulir.' }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('events.feedback.store', ['event' => $event->slug]) }}" class="space-y-5"
                    data-feedback-form data-csrf-refresh-url="{{ route('events.feedback.csrf-token', ['event' => $event->slug]) }}">
                    @csrf

                    <div class="hidden" aria-hidden="true" style="display: none;">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama <span
                                    class="text-rose-500" aria-hidden="true">*</span><span class="sr-only">
                                    (required)</span></label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                autocomplete="name" autocapitalize="words" inputmode="text" enterkeyhint="next"
                                spellcheck="false"
                                class="min-h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="Nama lengkap">
                            @error('name')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="organization"
                                class="mb-2 block text-sm font-semibold text-slate-700">Organisasi <span
                                    class="text-rose-500" aria-hidden="true">*</span><span class="sr-only">
                                    (required)</span></label>
                            <input id="organization" name="organization" type="text" value="{{ old('organization') }}"
                                required autocomplete="organization" autocapitalize="words" inputmode="text"
                                enterkeyhint="next" spellcheck="false"
                                class="min-h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="Nama perusahaan atau instansi">
                            @error('organization')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="position" class="mb-2 block text-sm font-semibold text-slate-700">Jabatan</label>
                            <input id="position" name="position" type="text" value="{{ old('position') }}"
                                autocomplete="organization-title" autocapitalize="words" inputmode="text"
                                enterkeyhint="next" spellcheck="false"
                                class="min-h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="Jabatan atau peran">
                            @error('position')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">Nomor yang bisa
                                dihubungi</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                                autocomplete="tel" inputmode="tel" enterkeyhint="next" spellcheck="false"
                                class="min-h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="Nomor WhatsApp atau telepon">
                            @error('phone')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                autocomplete="email" inputmode="email" enterkeyhint="next" spellcheck="false"
                                class="min-h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="nama@email.com">
                            @error('email')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="kesan_dan_pesan" class="mb-2 block text-sm font-semibold text-slate-700">Kesan
                                dan Pesan <span class="text-rose-500" aria-hidden="true">*</span><span
                                    class="sr-only"> (required)</span></label>
                            <textarea id="kesan_dan_pesan" name="kesan_dan_pesan" rows="7" required maxlength="5000"
                                autocomplete="off" autocapitalize="sentences" spellcheck="true" enterkeyhint="done"
                                class="w-full rounded-lg border-slate-300 bg-white px-4 py-3 text-base leading-7 text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-madeena-teal focus:ring-madeena-teal/20"
                                placeholder="Ceritakan pengalaman, masukan, atau harapan Anda untuk {{ $event->name }}">{{ old('kesan_dan_pesan') }}</textarea>
                            @error('kesan_dan_pesan')
                                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs leading-5 text-slate-500">Gunakan dikte suara jika lebih nyaman.</p>
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg bg-madeena-blue px-6 py-3 text-base font-bold text-white shadow-lg shadow-madeena-blue/20 transition hover:bg-madeena-blue/95 focus:outline-none focus:ring-4 focus:ring-madeena-blue/20">
                        <i class="fas fa-paper-plane text-sm" aria-hidden="true"></i>
                        <span>Kirim Feedback</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-feedback-form]');

            if (!form) {
                return;
            }

            form.addEventListener('submit', async (event) => {
                if (form.dataset.csrfReady === 'true') {
                    delete form.dataset.csrfReady;
                    return;
                }

                event.preventDefault();

                if (form.dataset.csrfRefreshing === 'true') {
                    return;
                }

                form.dataset.csrfRefreshing = 'true';

                try {
                    const response = await fetch(form.dataset.csrfRefreshUrl, {
                        cache: 'no-store',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const token = form.querySelector('input[name="_token"]');

                        if (token && typeof data.token === 'string' && data.token.length > 0) {
                            token.value = data.token;
                        }
                    }
                } catch (error) {
                    // Submit with the existing token if the refresh endpoint is temporarily unreachable.
                }

                form.dataset.csrfReady = 'true';
                delete form.dataset.csrfRefreshing;

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(event.submitter || undefined);
                    return;
                }

                form.submit();
            });
        });
    </script>
@endpush
