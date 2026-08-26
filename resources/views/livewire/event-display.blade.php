<div wire:poll.5s
    x-data="{
        interval: null,
        lastNewestId: null,
        lastScrollHeight: 0,
        scrollStep: 1,
        autoScroll: true,
        storageKey: 'madeena.event-{{ $event->slug }}.display.autoScroll',
        init() {
            this.autoScroll = this.readAutoScrollPreference();
            this.$watch('autoScroll', (value) => this.persistAutoScrollPreference(value));

            this.$nextTick(() => {
                this.lastNewestId = this.currentNewestId();
                this.lastScrollHeight = this.$refs.scroller?.scrollHeight ?? 0;
                this.interval = window.setInterval(() => this.tick(), 45);
            });
        },
        destroy() {
            if (this.interval) {
                window.clearInterval(this.interval);
            }
        },
        readAutoScrollPreference() {
            try {
                const stored = window.localStorage.getItem(this.storageKey);

                return stored === null ? true : stored === 'true';
            } catch (error) {
                return true;
            }
        },
        persistAutoScrollPreference(value) {
            try {
                window.localStorage.setItem(this.storageKey, value ? 'true' : 'false');
            } catch (error) {
                return;
            }
        },
        setAutoScroll(value) {
            this.autoScroll = value;

            if (value) {
                this.$nextTick(() => this.tick());
            }
        },
        currentNewestId() {
            return this.$refs.scroller?.querySelector('[data-feedback-message-id]')?.dataset.feedbackMessageId ?? null;
        },
        tick() {
            const scroller = this.$refs.scroller;

            if (! scroller) {
                return;
            }

            const newestId = this.currentNewestId();
            const scrollHeight = scroller.scrollHeight;

            if (this.lastNewestId && newestId && newestId !== this.lastNewestId) {
                const heightDelta = scrollHeight - this.lastScrollHeight;

                this.lastNewestId = newestId;

                if (this.autoScroll) {
                    scroller.scrollTop = 0;
                } else if (heightDelta > 0 && scroller.scrollTop > 0) {
                    scroller.scrollTop += heightDelta;
                }

                this.lastScrollHeight = scroller.scrollHeight;

                return;
            }

            if (! this.lastNewestId && newestId) {
                this.lastNewestId = newestId;
            }

            if (! this.autoScroll) {
                this.lastScrollHeight = scrollHeight;

                return;
            }

            if (scrollHeight <= scroller.clientHeight + 1) {
                scroller.scrollTop = 0;
                this.lastScrollHeight = scrollHeight;

                return;
            }

            const maxScroll = scrollHeight - scroller.clientHeight;

            if (scroller.scrollTop >= maxScroll - 1) {
                scroller.scrollTop = 0;
                this.lastScrollHeight = scrollHeight;

                return;
            }

            scroller.scrollTop += this.scrollStep;
            this.lastScrollHeight = scrollHeight;
        },
    }"
    class="flex h-screen w-full flex-col overflow-hidden bg-slate-950 font-sans text-white">
    <header class="shrink-0 border-b border-white/10 bg-madeena-blue px-4 py-3 shadow-lg sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-screen-2xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <div class="shrink-0 rounded-lg bg-white p-1.5 shadow-md">
                    <img src="{{ asset('images/logo-current.png') }}" alt="Madeena Logo"
                        class="h-10 w-auto object-contain sm:h-12">
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-xl font-black text-white sm:text-2xl lg:text-3xl">{{ $event->name }}</h1>
                    <p class="mt-0.5 text-sm font-semibold text-madeena-teal sm:text-base">
                        Live Kesan &amp; Pesan | <span
                            class="text-white/65">{{ now()->format('H:i:s') }}</span>
                    </p>
                </div>
            </div>

            <button type="button" @click="setAutoScroll(! autoScroll)" :aria-pressed="autoScroll.toString()"
                class="inline-flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-left shadow-sm transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-madeena-teal/25 sm:w-auto"
                aria-label="Toggle Auto Scroll">
                <span class="text-sm font-bold text-white sm:text-base">Auto Scroll</span>
                <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
                    :class="autoScroll ? 'bg-madeena-teal' : 'bg-white/25'">
                    <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition"
                        :class="autoScroll ? 'translate-x-5' : 'translate-x-0.5'"></span>
                </span>
            </button>
        </div>
    </header>

    <main x-ref="scroller"
        class="min-h-0 flex-1 overflow-y-auto bg-slate-950 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
        <div class="mx-auto flex max-w-screen-2xl flex-col gap-4 sm:gap-5">
            @forelse ($messages as $message)
                <article wire:key="{{ $message->id }}" data-feedback-message-id="{{ $message->id }}"
                    class="rounded-lg border border-white/10 bg-white/[0.06] p-4 shadow-lg shadow-black/20 sm:p-6 lg:p-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="break-words text-xl font-black text-white sm:text-2xl lg:text-3xl">
                                {{ $message->name }}
                            </h2>
                            @if ($message->organization)
                                <p class="mt-1 break-words text-sm font-semibold text-madeena-teal sm:text-base lg:text-lg">
                                    {{ $message->organization }}
                                </p>
                            @endif
                        </div>

                        <time datetime="{{ $message->created_at->toIso8601String() }}"
                            class="shrink-0 rounded-lg border border-white/10 bg-slate-900/70 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white/55">
                            {{ $message->created_at->diffForHumans() }}
                        </time>
                    </div>

                    <p class="mt-4 break-words text-xl font-extrabold leading-relaxed text-white sm:text-2xl lg:text-3xl">
                        {{ $message->kesan_dan_pesan }}
                    </p>
                </article>
            @empty
                <div
                    class="flex min-h-[48vh] flex-col items-center justify-center rounded-lg border border-dashed border-white/15 bg-white/[0.04] px-5 py-12 text-center">
                    <i class="fas fa-comment-dots mb-5 text-5xl text-madeena-teal sm:text-6xl" aria-hidden="true"></i>
                    <p class="text-2xl font-black text-white sm:text-3xl">Belum ada pesan yang masuk.</p>
                    <p class="mt-3 max-w-xl text-base leading-7 text-white/70 sm:text-lg">Jadilah yang pertama untuk
                        memberikan kesan dan pesan!</p>
                </div>
            @endforelse
        </div>
    </main>

    <footer class="shrink-0 border-t border-white/10 bg-slate-900 px-4 py-3 sm:px-6 sm:py-4 lg:px-8">
        <div class="mx-auto grid max-w-screen-2xl gap-3 sm:grid-cols-[1fr_auto] sm:items-center lg:grid-cols-[1fr_auto_auto]">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wide text-madeena-teal">Kesan dan pesan</p>
                <h2 class="mt-1 text-lg font-black text-white sm:text-xl lg:text-2xl">Kirim Feedback untuk {{ $event->name }}</h2>
                <p class="mt-1 hidden max-w-2xl text-sm leading-6 text-white/65 md:block">
                    Sampaikan kesan dan pesan Anda untuk {{ $event->name }}.
                </p>
            </div>

            <div class="min-w-0 rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 sm:px-4 sm:py-3">
                <p class="text-xs font-bold uppercase tracking-wide text-white/45">Kesan dan Pesan URL</p>
                <a href="{{ $event->getFeedbackUrl() }}" target="_blank" rel="noreferrer"
                    class="mt-1 block break-all text-sm font-black text-white transition hover:text-madeena-teal sm:text-base lg:text-lg">
                    {{ $event->getFeedbackUrl() }}
                </a>
            </div>

            <a href="{{ $event->getFeedbackUrl() }}" target="_blank" rel="noreferrer"
                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-lg border border-madeena-teal/60 bg-madeena-teal px-4 py-2 text-sm font-black text-white shadow-lg shadow-madeena-teal/10 transition hover:bg-madeena-teal/90 focus:outline-none focus:ring-4 focus:ring-madeena-teal/25 sm:col-span-2 lg:col-span-1">
                Buka Form Feedback
            </a>
        </div>
    </footer>
</div>
