@props([

    'rows' => 5,

    'placeholder' => '',

])

<textarea

    rows="{{ $rows }}"

    placeholder="{{ $placeholder }}"

    class="w-full resize-none rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 transition duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"></textarea>