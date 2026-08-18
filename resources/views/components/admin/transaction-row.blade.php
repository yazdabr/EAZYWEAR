@props(['transaction'])

@php
    $paymentColor = match(strtoupper($transaction['payment'] ?? '')){
        'QRIS' => 'bg-violet-100 text-violet-700',
        'CASH' => 'bg-emerald-100 text-emerald-700',
        'TRANSFER' => 'bg-sky-100 text-sky-700',
        'EDC' => 'bg-orange-100 text-orange-700',
        default => 'bg-slate-100 text-slate-700'
    };

    $total=(float)($transaction['total'] ?? 0);
@endphp

{{-- ========================================= --}}
{{-- TAMPILAN MOBILE: Berbentuk Card (Box)       --}}
{{-- ========================================= --}}
<div class="block border-b border-slate-200 bg-white p-4 transition hover:bg-slate-50/50 md:hidden">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="font-bold text-slate-900">
                {{ $transaction['invoice'] ?? '-' }}
            </p>
            <p class="mt-0.5 text-xs text-slate-400">
                {{ $transaction['date'] ?? '-' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $paymentColor }}">
                {{ $transaction['payment'] ?? '-' }}
            </span>
            <x-admin.badge-status status="{{ $transaction['status'] ?? '-' }}" />
        </div>
    </div>

    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
        <div>
            <p class="text-[11px] text-slate-400">Pelanggan</p>
            <p class="text-sm font-medium text-slate-800">
                {{ $transaction['customer'] ?? '-' }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-[11px] text-slate-400">Total Tagihan</p>
            <p class="text-sm font-bold text-[#AE7C18]">
                Rp {{ number_format($total,0,',','.') }}
            </p>
        </div>
    </div>

    {{-- TOMBOL AKSI MOBILE --}}
    <div class="mt-3 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
        <button
            type="button"
            @click="
                window.dispatchEvent(new CustomEvent('open-view-transaction',{
                    detail:{
                        id:@js($transaction['id'] ?? null),
                        invoice:@js($transaction['invoice'] ?? ''),
                        date:@js($transaction['date'] ?? ''),
                        customer:@js($transaction['customer'] ?? ''),
                        customer_phone:@js($transaction['customer_phone'] ?? ''),
                        customer_email:@js($transaction['customer_email'] ?? ''),
                        payment:@js($transaction['payment'] ?? ''),
                        status:@js($transaction['status'] ?? 'PENDING'),
                        subtotal:@js($transaction['subtotal'] ?? 0),
                        discount:@js($transaction['discount'] ?? 0),
                        shipping:@js($transaction['shipping'] ?? 0),
                        total:@js($transaction['total'] ?? 0),
                        items:@js($transaction['items'] ?? [])
                    }
                }));
            "
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100"
        >
            <x-heroicon-o-eye class="h-3.5 w-3.5 text-slate-500"/>
            <span>Lihat</span>
        </button>

        <button
            type="button"
            @click="
                window.dispatchEvent(new CustomEvent('open-delete-transaction', {
                    detail: {
                        id: @js($transaction['id'] ?? null),
                        invoice: @js($transaction['invoice'] ?? ''),
                        customer: @js($transaction['customer'] ?? ''),
                        total: @js($total),
                        status: @js($transaction['status'] ?? '')
                    }
                }));
            "
            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100"
        >
            <x-heroicon-o-trash class="h-3.5 w-3.5"/>
            <span>Hapus</span>
        </button>
    </div>
</div>


{{-- ========================================= --}}
{{-- TAMPILAN DESKTOP: Format Baris Tabel Asli --}}
{{-- ========================================= --}}
<tr class="hidden transition duration-200 hover:bg-slate-50 md:table-row">
    <td class="px-6 py-5">
        <p class="font-semibold text-slate-900">
            {{ $transaction['invoice'] ?? '-' }}
        </p>
    </td>

    <td class="px-6 py-5">
        <span class="text-sm text-slate-500">
            {{ $transaction['date'] ?? '-' }}
        </span>
    </td>

    <td class="px-6 py-5">
        <div>
            <p class="font-medium text-slate-900">
                {{ $transaction['customer'] ?? '-' }}
            </p>

            @if(!empty($transaction['email']))
                <p class="mt-1 text-xs text-slate-400">
                    {{ $transaction['email'] }}
                </p>
            @endif
        </div>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="font-bold text-[#AE7C18]">
            Rp {{ number_format($total,0,',','.') }}
        </span>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $paymentColor }}">
            {{ $transaction['payment'] ?? '-' }}
        </span>
    </td>

    <td class="px-6 py-5 text-center">
        <x-admin.badge-status status="{{ $transaction['status'] ?? '-' }}" />
    </td>

    <td class="px-6 py-5 text-center" 
        x-data="{ 
            open: false, 
            dropUp: false,
            toggleDropdown(event) {
                this.open = !this.open;
                if (this.open) {
                    let rect = event.currentTarget.getBoundingClientRect();
                    let windowHeight = window.innerHeight;
                    // Jika jarak tombol ke bawah layar kurang dari 220px, buka ke atas (dropUp)
                    this.dropUp = (windowHeight - rect.bottom) < 220;
                }
            }
        }" 
        @resize.window="open = false"
    >
        <div class="relative inline-block text-left">
            <button
                type="button"
                @click="toggleDropdown($event)"
                title="Aksi"
                class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                :class="open ? 'bg-slate-100' : ''"
            >
                <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :class="dropUp ? 'bottom-full mb-2 origin-bottom-right' : 'top-full mt-2 origin-top-right'"
                class="absolute right-0 z-[999] w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-2xl shadow-slate-900/20"
                style="display:none;"
            >
                <button
                    type="button"
                    @click="
                        open=false;
                        window.dispatchEvent(new CustomEvent('open-view-transaction',{
                            detail:{
                                id:@js($transaction['id'] ?? null),
                                invoice:@js($transaction['invoice'] ?? ''),
                                date:@js($transaction['date'] ?? ''),
                                customer:@js($transaction['customer'] ?? ''),
                                customer_phone:@js($transaction['customer_phone'] ?? ''),
                                customer_email:@js($transaction['customer_email'] ?? ''),
                                payment:@js($transaction['payment'] ?? ''),
                                status:@js($transaction['status'] ?? 'PENDING'),
                                subtotal:@js($transaction['subtotal'] ?? 0),
                                discount:@js($transaction['discount'] ?? 0),
                                shipping:@js($transaction['shipping'] ?? 0),
                                total:@js($transaction['total'] ?? 0),
                                items:@js($transaction['items'] ?? [])
                            }
                        }));
                    "
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500"/>
                    <span>Lihat</span>
                </button>

                <button
                    type="button"
                    @click="
                        open = false;
                        window.dispatchEvent(new CustomEvent('open-delete-transaction', {
                            detail: {
                                id: @js($transaction['id'] ?? null),
                                invoice: @js($transaction['invoice'] ?? ''),
                                customer: @js($transaction['customer'] ?? ''),
                                total: @js($total),
                                status: @js($transaction['status'] ?? '')
                            }
                        }));
                    "
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                >
                    <x-heroicon-o-trash class="h-4 w-4 shrink-0"/>
                    <span>Hapus</span>
                </button>
            </div>
        </div>
    </td>
</tr>