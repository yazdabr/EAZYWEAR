<div
    x-data="{

        open:false,

        transaction:{

            invoice:'',
            date:'',
            customer:'',
            phone:'',
            email:'',
            payment:'',
            status:'',
            subtotal:'',
            discount:'',
            shipping:'',
            total:'',
            items:[]

        },

        openDrawer(data){

            this.transaction = data;

            this.open = true;

        }

    }"

    x-effect="document.body.classList.toggle('overflow-hidden', open)"

    @keydown.escape.window="open=false"

    x-on:open-view-transaction.window="openDrawer($event.detail)">

    {{-- Overlay --}}
    <div

        x-show="open"

        x-transition.opacity

        @click="open=false"

        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"

        style="display:none;">

    </div>

    {{-- Drawer --}}
    <div

        x-show="open"

        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"

        x-transition:enter-start="translate-x-full"

        x-transition:enter-end="translate-x-0"

        x-transition:leave="transition transform duration-300 ease-in-out"

        x-transition:leave-start="translate-x-0"

        x-transition:leave-end="translate-x-full"

        class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[760px] flex-col bg-white shadow-2xl"

        style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-8 py-6">

            <div>

                <p class="text-sm font-medium text-slate-500">

                    Transaction Details

                </p>

                <h2

                    class="mt-1 text-2xl font-bold text-slate-900"

                    x-text="transaction.invoice">

                </h2>

            </div>

            <button

                @click="open=false"

                class="rounded-xl p-2 transition hover:bg-slate-100">

                <x-heroicon-o-x-mark class="h-6 w-6"/>

            </button>

        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-8 overflow-y-auto bg-slate-50 p-8">

            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6">

                <h3 class="mb-5 text-lg font-semibold text-slate-900">

                    Customer Information

                </h3>

                <div class="grid gap-5 md:grid-cols-2">

                    <div>

                        <p class="text-xs uppercase tracking-wider text-slate-400">

                            Customer

                        </p>

                        <p

                            class="mt-1 font-semibold text-slate-900"

                            x-text="transaction.customer">

                        </p>

                    </div>

                    <div>

                        <p class="text-xs uppercase tracking-wider text-slate-400">

                            Phone

                        </p>

                        <p

                            class="mt-1 font-semibold text-slate-900"

                            x-text="transaction.phone">

                        </p>

                    </div>

                    <div>

                        <p class="text-xs uppercase tracking-wider text-slate-400">

                            Email

                        </p>

                        <p

                            class="mt-1 font-semibold text-slate-900"

                            x-text="transaction.email">

                        </p>

                    </div>

                    <div>

                        <p class="text-xs uppercase tracking-wider text-slate-400">

                            Date

                        </p>

                        <p

                            class="mt-1 font-semibold text-slate-900"

                            x-text="transaction.date">

                        </p>

                    </div>

                </div>

            </div>

            {{-- Products --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6">

                <h3 class="mb-5 text-lg font-semibold text-slate-900">

                    Products

                </h3>

                <table class="min-w-full">

                    <thead>

                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">

                            <th class="pb-3">

                                Product

                            </th>

                            <th class="pb-3">

                                Size

                            </th>

                            <th class="pb-3">

                                Color

                            </th>

                            <th class="pb-3 text-center">

                                Qty

                            </th>

                            <th class="pb-3 text-right">

                                Total

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <template
                            x-for="item in transaction.items">

                            <tr class="border-b border-slate-100">

                                <td class="py-4">

                                    <p
                                        class="font-medium text-slate-900"

                                        x-text="item.name">

                                    </p>

                                </td>

                                <td
                                    class="py-4"

                                    x-text="item.size">

                                </td>

                                <td
                                    class="py-4"

                                    x-text="item.color">

                                </td>

                                <td
                                    class="py-4 text-center"

                                    x-text="item.qty">

                                </td>

                                <td
                                    class="py-4 text-right font-semibold"

                                    x-text="item.total">

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

            {{-- Payment --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- ================= PAYMENT ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">

                    <h3 class="mb-5 text-lg font-semibold text-slate-900">

                        Payment

                    </h3>

                    <div class="space-y-6">

                        {{-- Payment Method --}}
                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">

                                Payment Method

                            </span>

                            <span

                                class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700"

                                x-text="transaction.payment">

                            </span>

                        </div>

                        {{-- Transaction Status --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">

                                Transaction Status

                            </label>

                            <select

                                x-model="transaction.status"

                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">

                                <option value="Pending">Pending</option>

                                <option value="Paid">Paid</option>

                                <option value="Processing">Processing</option>

                                <option value="Completed">Completed</option>

                                <option value="Cancelled">Cancelled</option>

                            </select>

                        </div>

                    </div>

                </div>

                {{-- ================= SUMMARY ================= --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6">

                    <h3 class="mb-5 text-lg font-semibold text-slate-900">

                        Summary

                    </h3>

                    <div class="space-y-4">

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">

                                Subtotal

                            </span>

                            <span

                                class="font-medium text-slate-900"

                                x-text="transaction.subtotal">

                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">

                                Discount

                            </span>

                            <span

                                class="font-medium text-slate-900"

                                x-text="transaction.discount">

                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-slate-500">

                                Shipping

                            </span>

                            <span

                                class="font-medium text-slate-900"

                                x-text="transaction.shipping">

                            </span>

                        </div>

                        <div class="border-t border-dashed border-slate-300 pt-4">

                            <div class="flex items-center justify-between">

                                <span class="text-lg font-bold text-slate-900">

                                    Grand Total

                                </span>

                                <span

                                    class="text-2xl font-bold text-[#AE7C18]"

                                    x-text="transaction.total">

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Footer --}}
        <div
            class="flex items-center justify-between border-t border-slate-200 bg-white px-8 py-5">

            <button

                @click="

                    $dispatch('toast',{

                        type:'success',

                        title:'Transaction Updated',

                        message:'Transaction status updated successfully.'

                    });

                "

                class="rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">

                Update Status

            </button>

            <div class="flex gap-3">

                <a

                    :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)"

                    target="_blank"

                    class="rounded-xl border border-slate-300 px-5 py-3 font-medium transition hover:bg-slate-100">

                    Print Invoice

                </a>

                <button

                    @click="open=false"

                    class="rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white transition hover:bg-[#96690F]">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>