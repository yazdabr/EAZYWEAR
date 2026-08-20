<div>
    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
        Bulan
    </label>

    <select
        name="month"
        onchange="this.form.submit()"
        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
    >
        <option value="">Semua Bulan</option>
        @foreach(range(1,12) as $month)
            <option
                value="{{ $month }}"
                @selected((string) request('month') === (string) $month)
            >
                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
        Tahun
    </label>

    <select
        name="year"
        onchange="this.form.submit()"
        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
    >
        <option value="">Semua Tahun</option>

        @foreach($years as $year)
            <option
                value="{{ $year }}"
                @selected((string) request('year') === (string) $year)
            >
                {{ $year }}
            </option>
        @endforeach
    </select>
</div>