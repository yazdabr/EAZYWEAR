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

<td
    class="px-6 py-5 text-center"
    x-data="{
        open:false,
        dropUp:false,
        top:0,
        left:0,
        width:176,
        toggleDropdown(event){
            const rect=event.currentTarget.getBoundingClientRect();
            this.open=!this.open;

            if(this.open){
                const menuHeight=100;
                this.dropUp=(window.innerHeight-rect.bottom)<menuHeight+20;
                this.width=176;
                this.left=rect.right-this.width;
                this.top=this.dropUp
                    ? rect.top-menuHeight-8
                    : rect.bottom+8;
            }
        },
        close(){
            this.open=false;
        }
    }"
    @resize.window="close()"
    @scroll.window="close()"
>
    <button
        type="button"
        @click="toggleDropdown($event)"
        title="Aksi"
        class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
        :class="open?'bg-slate-100':''"
    >
        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @click.outside="open=false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :style="`
                position:fixed;
                top:${top}px;
                left:${left}px;
                width:${width}px;
            `"
            class="z-[999999] overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-2xl shadow-slate-900/20"
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

                            shipping_address:@js($transaction['shipping_address'] ?? ''),
                            shipping_district:@js($transaction['shipping_district'] ?? ''),
                            shipping_city:@js($transaction['shipping_city'] ?? ''),
                            shipping_province:@js($transaction['shipping_province'] ?? ''),
                            shipping_postal_code:@js($transaction['shipping_postal_code'] ?? ''),
                            shipping_method:@js($transaction['shipping_method'] ?? ''),

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
                    open=false;
                    window.dispatchEvent(new CustomEvent('open-delete-transaction',{
                        detail:{
                            id:@js($transaction['id'] ?? null),
                            invoice:@js($transaction['invoice'] ?? ''),
                            customer:@js($transaction['customer'] ?? ''),
                            total:@js($total),
                            status:@js($transaction['status'] ?? '')
                        }
                    }));
                "
                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
            >
                <x-heroicon-o-trash class="h-4 w-4 shrink-0"/>
                <span>Hapus</span>
            </button>
        </div>
    </template>
</td>
</tr>