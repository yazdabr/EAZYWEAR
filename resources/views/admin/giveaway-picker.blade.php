@extends('admin.layouts.app')
@section('title', 'Giveaway Picker')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[.2em] text-[#C4902C]">Eazywear</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Instagram Giveaway Picker</h1><p class="mt-1 text-sm text-slate-500">Filter komentar dan undi pemenang secara acak.</p></div>
        {{-- <span class="inline-flex w-fit items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Integrasi API siap diuji</span> --}}
    </div>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-[#C4902C]"><x-heroicon-o-link class="h-5 w-5"/></div><div><h2 class="font-bold text-slate-900">Sumber Giveaway</h2><p class="text-xs text-slate-500">Masukkan postingan Instagram yang akan diundi.</p></div></div>
                <label for="ig-post-url" class="mb-2 block text-sm font-semibold text-slate-700">Link postingan Instagram</label>
                <input id="ig-post-url" type="url" placeholder="https://www.instagram.com/p/xxxxxxxx/" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#C4902C] focus:ring-2 focus:ring-amber-100">
                <div class="mt-5 grid items-stretch gap-4 sm:grid-cols-2">
                    <div class="flex flex-col">
                        <label for="comment-filter" class="mb-2 block text-sm font-semibold text-slate-700">
                            Filter isi komentar
                        </label>
                        <input
                            id="comment-filter"
                            type="text"
                            value="1-1"
                            placeholder="Contoh: 1-1"
                            class="h-[46px] w-full rounded-xl border border-slate-300 px-4 text-sm outline-none focus:border-[#C4902C] focus:ring-2 focus:ring-amber-100"
                        >
                        <p class="mt-1.5 text-xs text-slate-400">
                            Kosongkan jika semua komentar ingin diikutkan.
                        </p>
                    </div>

                    <div class="flex flex-col">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Opsi peserta
                        </label>
                        <label class="flex h-[46px] cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 text-sm text-slate-700">
                            <input
                                id="unique-user"
                                type="checkbox"
                                checked
                                class="h-4 w-4 shrink-0 rounded border-slate-300 text-[#C4902C] focus:ring-[#C4902C]"
                            >
                            <span>
                                <strong class="block">Satu akun satu kesempatan</strong>
                            </span>
                        </label>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Komentar pertama yang cocok dihitung.
                        </p>
                    </div>
                </div>
                <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50/60 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-bold text-slate-800">Ambil komentar Instagram</p><p class="text-xs text-slate-500">Ambil komentar asli dari postingan akun Instagram yang terhubung.</p></div><button id="fetch-instagram" type="button" class="rounded-lg bg-[#C4902C] px-3 py-2 text-xs font-bold text-white hover:bg-[#AD7D20]">Ambil Komentar Instagram</button></div>
                    <textarea id="comments-input" rows="5" placeholder="@username|Prediksi saya 2-1&#10;@username2|Skor 1-0" class="mt-3 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 font-mono text-xs outline-none focus:border-[#C4902C] focus:ring-2 focus:ring-amber-100"></textarea>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3"><p id="import-status" class="text-xs text-slate-500">Belum ada komentar dimuat.</p><button id="apply-filter" type="button" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan Filter</button></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-bold text-slate-900">Peserta Undian</h2><p class="text-xs text-slate-500">Daftar komentar yang memenuhi filter.</p></div><div class="flex gap-2"><div class="rounded-xl bg-slate-50 px-3 py-2 text-center"><strong id="total-comments" class="block text-lg text-slate-900">0</strong><span class="text-[10px] text-slate-500">Komentar</span></div><div class="rounded-xl bg-amber-50 px-3 py-2 text-center"><strong id="eligible-count" class="block text-lg text-amber-700">0</strong><span class="text-[10px] text-amber-700">Peserta lolos</span></div></div></div>
                <div class="mt-4 overflow-hidden rounded-xl border border-slate-100">
                    <div class="grid grid-cols-[minmax(90px,.7fr)_minmax(0,1.5fr)_minmax(130px,1fr)] gap-3 bg-slate-50 px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <span>Username</span>
                        <span>Komentar</span>
                        <span>Tanggal & Jam</span>
                    </div>

                    <div id="participants-list" class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                        <div class="px-4 py-8 text-center text-sm text-slate-400">
                            Belum ada peserta. Muat data contoh atau masukkan komentar.
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <aside class="space-y-6">
            <div class="overflow-hidden rounded-2xl bg-[#171717] text-white shadow-lg">
                <div class="border-b border-white/10 px-5 py-4"><p class="text-xs font-bold uppercase tracking-[.18em] text-[#D6A84D]">Live draw</p><h2 class="mt-1 text-lg font-bold">Pengundian Pemenang</h2></div>
                <div class="p-5"><div class="flex items-center justify-between text-xs text-white/50"><span>PEMENANG BERIKUTNYA</span><span id="winner-progress">0 / 3</span></div>
                    <div class="my-5 flex min-h-48 flex-col items-center justify-center rounded-2xl border border-white/10 bg-white/[.04] px-4 py-6 text-center">
                        <div id="draw-placeholder"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/10 text-[#D6A84D]"><x-heroicon-o-gift class="h-7 w-7"/></div><p class="mt-4 text-sm font-semibold text-white/80">Siap menentukan pemenang?</p><p class="mt-1 text-xs text-white/40">Pemenang akan muncul di sini.</p></div>
                        <div id="rolling-name" class="hidden text-2xl font-black tracking-wide text-[#E7BD68]"></div>
                        <div id="winner-result" class="hidden w-full"><p id="winner-number" class="text-[10px] font-bold uppercase tracking-[.2em] text-[#D6A84D]"></p><p id="winner-username" class="mt-3 break-all text-2xl font-black text-white"></p><div class="mt-4 rounded-xl bg-black/30 p-3 text-left"><p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Komentar terpilih</p><p id="winner-comment" class="mt-1 break-words text-sm leading-relaxed text-white/90"></p>
                        <p id="winner-date" class="mt-2 text-xs text-white/50"></p></div></div>
                    </div>
                    <button id="draw-button" type="button" disabled class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#C4902C] px-4 py-3.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-[#AD7D20] disabled:cursor-not-allowed disabled:opacity-40">UNDI PEMENANG</button>
                    <p id="draw-hint" class="mt-3 text-center text-[11px] leading-relaxed text-white/40">Terapkan filter dan pastikan peserta tersedia untuk memulai undian.</p>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h3 class="font-bold text-slate-900">Riwayat Pemenang</h3><button id="reset-draw" type="button" class="text-xs font-semibold text-slate-400 hover:text-red-600">Reset</button></div><div id="winner-history" class="mt-4 space-y-3"><p class="text-sm text-slate-400">Belum ada pemenang.</p></div></div>
        </aside>
    </div>

    <div id="delete-winner-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="delete-winner-title">
        <div id="delete-winner-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100/80">
                <x-heroicon-o-trash class="h-7 w-7 text-red-600"/>
            </div>
            <div class="mt-4 text-center">
                <h2 id="delete-winner-title" class="text-lg font-bold text-slate-900">Hapus Pemenang?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Pemenang <span id="delete-winner-name" class="font-bold break-words text-slate-900"></span> akan dihapus dari riwayat dan tidak diikutsertakan pada undian berikutnya.
                </p>
            </div>
            <div class="mt-6 flex gap-2.5">
                <button id="delete-winner-cancel" type="button" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                <button id="delete-winner-confirm" type="button" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-500/20 transition hover:bg-red-700">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(() => {
    const $ = id => document.getElementById(id);
    const commentsEndpoint = @json(route('admin.giveaway-picker.comments'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const demo = [
        ['fajar_12','Prediksi 2-1, Eazywear juara!'],['nisaaa','2-1 semoga beruntung'],['andika.store','Skor akhir 1-0'],
        ['raka_awan','Saya pilih 2-1'],['putri.ram','2-1'],['bima_pratama','Prediksi saya 2-1'],
        ['dianaaa','3-1 untuk kemenangan!'],['rezafootball','2-1 mantap'],['aldi_aja','Bismillah 2-1'],
        ['salsa.m','1-1'],['yoga_19','Tebakan 2-1'],['nanda_put','2-0']
    ];
    let allComments = [], eligible = [], winners = [], excludedWinners = new Set(), drawing = false;
    let displayedWinner = null, pendingWinnerIndex = null;
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    }
    function participantKey(value) {
        return String(value).toLocaleLowerCase();
    }
    function isCurrentWinner(item) {
        return winners.some(winner => participantKey(winner.username) === participantKey(item.username));
    }
    function availableParticipants() {
        return eligible.filter(item => !isCurrentWinner(item) && !excludedWinners.has(participantKey(item.username)));
    }
    function formatCommentDate(value) {
        if (!value) return 'Waktu tidak tersedia';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return 'Waktu tidak tersedia';
        }

        return new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Makassar',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(date) + ' WITA';
    }
    function normalizeScore(value) {
        return String(value)
            .toLocaleLowerCase()
            .replace(
                /(\d+)\s*(?:[-–—−:：/]\s*|\s+)(\d+)/g,
                '$1-$2'
            );
    }
    function parseComments() {
        const previousComments = new Map(
            allComments.map(item => [
                `${item.username.toLowerCase()}|${item.text}`,
                item.created_at || null
            ])
        );

        return $('comments-input').value.split(/\r?\n/).map(line => {
            const separator = line.indexOf('|');
            if (separator < 1) return null;

            const username = line.slice(0, separator).trim().replace(/^@/, '');
            const text = line.slice(separator + 1).trim();

            return username && text
                ? {
                    username,
                    text,
                    created_at: previousComments.get(
                        `${username.toLowerCase()}|${text}`
                    ) || null
                }
                : null;
        }).filter(Boolean);
    }
    function updateDrawButton() {
        const remaining = availableParticipants();
        $('draw-button').disabled = drawing || remaining.length === 0 || winners.length >= 3;
        $('winner-progress').textContent = winners.length + ' / 3';
        $('draw-button').textContent = winners.length >= 3 ? 'UNDIAN SELESAI' : winners.length ? 'UNDI PEMENANG BERIKUTNYA' : 'UNDI PEMENANG';
        $('draw-hint').textContent = winners.length >= 3
            ? 'Tiga pemenang sudah terpilih.'
            : remaining.length
                ? remaining.length + ' peserta masih tersedia.'
                : eligible.length
                    ? 'Tidak ada peserta tersisa untuk diundi.'
                    : 'Peserta belum tersedia.';
    }
    function renderHistory() {
        $('winner-history').innerHTML = winners.length ? winners.map((winner, index) =>
            `<div class="flex items-start gap-3 rounded-xl bg-slate-50 p-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-xs font-black text-amber-800">${index + 1}</div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-800">@${escapeHtml(winner.username)}</p>
                    <p class="mt-0.5 text-xs text-slate-500">${escapeHtml(winner.text)}</p>
                    <p class="mt-2 text-[11px] text-slate-400">${escapeHtml(formatCommentDate(winner.created_at))}</p>
                </div>
                <button type="button" data-remove-winner="${index}" ${drawing ? 'disabled' : ''} class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40" title="Hapus pemenang" aria-label="Hapus pemenang @${escapeHtml(winner.username)}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7m2 0-.7 11.1A2 2 0 0 1 14.3 20H9.7a2 2 0 0 1-2-1.9L7 7m3 3.5v6m4-6v6"/></svg>
                </button>
            </div>`
        ).join('') : '<p class="text-sm text-slate-400">Belum ada pemenang.</p>';

        updateDrawButton();
    }
    function applyFilter() {
        const term = normalizeScore($('comment-filter').value.trim());

        const filtered = allComments.filter(item =>
            !term || normalizeScore(item.text).includes(term)
        );
        if ($('unique-user').checked) {
            const seen = new Set();
            eligible = filtered.filter(item => {
                const key = item.username.toLocaleLowerCase();
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
        } else eligible = filtered;
        winners = [];
        excludedWinners.clear();
        displayedWinner = null;
        $('total-comments').textContent = allComments.length;
        $('eligible-count').textContent = eligible.length;
        $('import-status').textContent = allComments.length + ' komentar dimuat · ' + eligible.length + ' peserta memenuhi filter';
        $('participants-list').innerHTML = eligible.length
            ? eligible.slice(0, 100).map(item => `
                <div class="grid grid-cols-[minmax(90px,.7fr)_minmax(0,1.5fr)_minmax(130px,1fr)] gap-3 px-4 py-3 text-sm">
                    <strong class="truncate text-slate-800">
                        @${escapeHtml(item.username)}
                    </strong>

                    <span class="break-words text-slate-500">
                        ${escapeHtml(item.text)}
                    </span>

                    <span class="text-xs text-slate-500">
                        ${escapeHtml(formatCommentDate(item.created_at))}
                    </span>
                </div>
            `).join('') + (eligible.length > 100 ? '<p class="bg-slate-50 px-4 py-2 text-center text-xs text-slate-400">Menampilkan 100 dari ' + eligible.length + ' peserta.</p>' : '')
            : '<div class="px-4 py-8 text-center text-sm text-slate-400">Tidak ada komentar yang memenuhi filter.</div>';
        $('draw-placeholder').classList.remove('hidden');
        $('winner-result').classList.add('hidden');
        renderHistory();
    }
    $('fetch-instagram').addEventListener('click', async () => {
        const postUrl = $('ig-post-url').value.trim();
        if (!postUrl) {
            $('import-status').textContent = 'Masukkan link postingan Instagram terlebih dahulu.';
            return;
        }
        const button = $('fetch-instagram');
        button.disabled = true;
        button.textContent = 'Mengambil komentar...';
        $('import-status').textContent = 'Menghubungi Instagram Graph API...';
        try {
            const response = await fetch(commentsEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ post_url: postUrl })
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Gagal mengambil komentar Instagram.');
            allComments = (payload.comments || []).map(item => ({
                username: item.username,
                text: item.text,
                created_at: item.created_at || null
            }));
            $('comments-input').value = allComments.map(item => '@' + item.username + '|' + item.text).join('\n');
            applyFilter();
            $('import-status').textContent = payload.total + ' komentar berhasil diambil dari Instagram.';
        } catch (error) {
            $('import-status').textContent = error.message;
        } finally {
            button.disabled = false;
            button.textContent = 'Ambil Komentar Instagram';
        }
    });
    $('apply-filter').addEventListener('click', () => { allComments = parseComments(); applyFilter(); });
    $('unique-user').addEventListener('change', () => { if (allComments.length) applyFilter(); });
    $('draw-button').addEventListener('click', () => {
        if (drawing) return;
        const pool = availableParticipants();
        if (!pool.length || winners.length >= 3) return;
        drawing = true; updateDrawButton();
        $('draw-placeholder').classList.add('hidden'); $('winner-result').classList.add('hidden'); $('rolling-name').classList.remove('hidden');
        let ticks = 0;
        const rolling = setInterval(() => {
            const preview = pool[Math.floor(Math.random()*pool.length)];
            $('rolling-name').textContent = '@' + preview.username;
            ticks++;
            if (ticks >= 28) {
                clearInterval(rolling);
                const winner = pool[Math.floor(Math.random()*pool.length)];
                winners.push(winner);
                displayedWinner = winner;
                $('rolling-name').classList.add('hidden'); $('winner-result').classList.remove('hidden');
                $('winner-number').textContent = 'PEMENANG ' + winners.length;
                $('winner-username').textContent = '@' + winner.username;
                $('winner-comment').textContent = winner.text;
                $('winner-date').textContent = formatCommentDate(winner.created_at);
                drawing = false; renderHistory();
            }
        },75);
    });
    function openDeleteWinnerModal(index) {
        if (drawing || !winners[index]) return;
        pendingWinnerIndex = index;
        $('delete-winner-name').textContent = '@' + winners[index].username;
        const modal = $('delete-winner-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        $('delete-winner-confirm').focus();
    }
    function closeDeleteWinnerModal() {
        pendingWinnerIndex = null;
        const modal = $('delete-winner-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }
    function deleteWinner() {
        if (pendingWinnerIndex === null || drawing) return;
        const removed = winners[pendingWinnerIndex];
        closeDeleteWinnerModal();
        if (!removed) return;
        winners.splice(winners.indexOf(removed), 1);
        excludedWinners.add(participantKey(removed.username));

        if (displayedWinner === removed) {
            displayedWinner = null;
            $('winner-result').classList.add('hidden');
            $('rolling-name').classList.add('hidden');
            $('draw-placeholder').classList.remove('hidden');
        } else if (displayedWinner && !$('winner-result').classList.contains('hidden')) {
            const displayedIndex = winners.indexOf(displayedWinner);
            if (displayedIndex !== -1) $('winner-number').textContent = 'PEMENANG ' + (displayedIndex + 1);
        }

        renderHistory();
    }
    $('winner-history').addEventListener('click', event => {
        const button = event.target.closest('[data-remove-winner]');
        if (!button || drawing) return;
        openDeleteWinnerModal(Number(button.dataset.removeWinner));
    });
    $('delete-winner-cancel').addEventListener('click', closeDeleteWinnerModal);
    $('delete-winner-overlay').addEventListener('click', closeDeleteWinnerModal);
    $('delete-winner-confirm').addEventListener('click', deleteWinner);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && pendingWinnerIndex !== null) closeDeleteWinnerModal();
    });
    $('reset-draw').addEventListener('click', () => {
        if (drawing) return;
        winners = []; excludedWinners.clear(); displayedWinner = null;
        $('winner-result').classList.add('hidden'); $('rolling-name').classList.add('hidden');
        $('draw-placeholder').classList.remove('hidden'); renderHistory();
    });
})();
</script>
@endpush
