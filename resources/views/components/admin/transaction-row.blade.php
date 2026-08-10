@props([
    'transaction'
])

<tr class="transition duration-200 hover:bg-slate-50">

    {{-- Invoice --}}
    <td class="px-6 py-5">
        <div>
            <p class="font-semibold text-slate-900">
                {{ $transaction['invoice'] }}
            </p>
        </div>
    </td>

    {{-- Date --}}
    <td class="px-6 py-5">
        <span class="text-sm text-slate-500">
            {{ $transaction['date'] }}
        </span>
    </td>

    {{-- Customer --}}
    <td class="px-6 py-5">
        <div>
            <p class="font-medium text-slate-900">
                {{ $transaction['customer'] }}
            </p>
        </div>
    </td>

    {{-- Total --}}
    <td class="px-6 py-5 text-center">
        <span class="font-bold text-[#AE7C18]">
            {{ $transaction['total'] }}
        </span>
    </td>

    {{-- Payment --}}
    <td class="px-6 py-5 text-center">
        @php
            $paymentColor = match($transaction['payment']){
                'QRIS'     => 'bg-violet-100 text-violet-700',
                'Cash'     => 'bg-emerald-100 text-emerald-700',
                'Transfer' => 'bg-sky-100 text-sky-700',
                'EDC'      => 'bg-orange-100 text-orange-700',
                default    => 'bg-slate-100 text-slate-700'
            };
        @endphp

        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $paymentColor }}">
            {{ $transaction['payment'] }}
        </span>
    </td>

    {{-- Status --}}
    <td class="px-6 py-5 text-center">
        <x-admin.badge-status status="{{ $transaction['status'] }}" />
    </td>

    {{-- Action --}}
    <td class="px-6 py-5 text-center">

        <div
            x-data="{ open: false }"
            class="relative inline-block text-left">

            {{-- Tombol Aksi --}}
            <button
                type="button"
                @click="open = !open"
                title="Aksi"

                class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"

                :class="open ? 'bg-slate-100' : ''">

                <x-heroicon-o-ellipsis-horizontal
                    class="h-5 w-5 text-slate-500" />

            </button>


            {{-- Dropdown Aksi --}}
            <div
                x-show="open"

                @click.outside="open = false"

                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"

                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"

                class="absolute right-0 top-full z-[80] mt-2 w-44 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-900/10"

                style="display:none;">


                {{-- ================= LIHAT ================= --}}
                <button
                    type="button"

                    @click="
                        open = false;

                        $dispatch('open-view-transaction', {

                            invoice: @js($transaction['invoice']),

                            date: @js($transaction['date']),

                            customer: @js($transaction['customer']),

                            phone: '0812-3456-7890',

                            email: 'customer@example.com',

                            payment: @js($transaction['payment']),

                            status: @js($transaction['status']),

                            subtotal: @js($transaction['total']),

                            discount: 'Rp 0',

                            shipping: 'Rp 20.000',

                            total: @js($transaction['total']),

                            items: [

                                {
                                    name: 'Apex Pro Jersey',
                                    size: 'XL',
                                    color: 'Black',
                                    qty: 2,
                                    total: 'Rp 298.000'
                                },

                                {
                                    name: 'Elite Training Jersey',
                                    size: 'L',
                                    color: 'White',
                                    qty: 1,
                                    total: 'Rp 199.000'
                                }

                            ]

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-eye
                        class="h-4 w-4 shrink-0 text-slate-500" />

                    <span>
                        Lihat
                    </span>

                </button>


                {{-- ================= HAPUS ================= --}}
                <button
                    type="button"

                    @click="
                        open = false;

                        $dispatch('open-delete-transaction', {

                            invoice: @js($transaction['invoice']),

                            customer: @js($transaction['customer']),

                            total: @js($transaction['total']),

                            status: @js($transaction['status'])

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">

                    <x-heroicon-o-trash
                        class="h-4 w-4 shrink-0" />

                    <span>
                        Hapus
                    </span>

                </button>

            </div>

        </div>

    </td>

</tr>