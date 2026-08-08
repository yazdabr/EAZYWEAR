<div
    x-data="{

        open:false,

        transaction:{},

        openModal(data){

            this.transaction = data;

            this.open = true;

        }

    }"

    @keydown.escape.window="open=false"

    x-on:open-delete-transaction.window="openModal($event.detail)">

    {{-- Overlay --}}
    <div

        x-show="open"

        x-transition.opacity

        @click="open=false"

        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"

        style="display:none;">

    </div>

    {{-- Modal --}}
    <div

        x-show="open"

        x-transition

        class="fixed inset-0 z-[100] flex items-center justify-center p-6"

        style="display:none;">

        <div

            @click.stop

            class="w-full max-w-md rounded-3xl bg-white shadow-2xl">

            {{-- Header --}}
            <div class="border-b border-slate-200 px-8 py-6">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">

                    <x-heroicon-o-exclamation-triangle
                        class="h-7 w-7 text-red-600"/>

                </div>

                <h2 class="mt-5 text-center text-2xl font-bold text-slate-900">

                    <span
                        x-text="transaction.status === 'Pending'
                            ? 'Cancel Transaction'
                            : 'Delete Transaction'">

                    </span>

                </h2>

                <p class="mt-3 text-center text-sm text-slate-500">

                    Invoice

                    <span

                        class="font-semibold text-slate-700"

                        x-text="transaction.invoice">

                    </span>

                    will be

                    <span
                        x-text="transaction.status === 'Pending'
                            ? 'cancelled'
                            : 'deleted'">

                    </span>.

                </p>

            </div>

            {{-- Body --}}
            <div class="px-8 py-6">

                <div class="rounded-2xl bg-slate-50 p-5">

                    <div class="flex justify-between">

                        <span class="text-slate-500">

                            Customer

                        </span>

                        <span

                            class="font-semibold"

                            x-text="transaction.customer">

                        </span>

                    </div>

                    <div class="mt-3 flex justify-between">

                        <span class="text-slate-500">

                            Total

                        </span>

                        <span

                            class="font-semibold text-[#AE7C18]"

                            x-text="transaction.total">

                        </span>

                    </div>

                </div>

            </div>

            {{-- Footer --}}
            <div class="flex gap-3 border-t border-slate-200 px-8 py-5">

                <button

                    @click="open=false"

                    class="flex-1 rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                    Close

                </button>

                <button

                    @click="

                        open=false;

                        setTimeout(()=>{

                            $dispatch('toast',{

                                type: transaction.status === 'Pending'
                                    ? 'warning'
                                    : 'error',

                                title: transaction.status === 'Pending'
                                    ? 'Transaction Cancelled'
                                    : 'Transaction Deleted',

                                message: transaction.status === 'Pending'
                                    ? 'Transaction has been cancelled.'
                                    : 'Transaction has been deleted.'

                            });

                        },300);

                    "

                    class="flex-1 rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">

                    <span

                        x-text="transaction.status==='Pending'
                            ? 'Cancel Transaction'
                            : 'Delete Transaction'">

                    </span>

                </button>

            </div>

        </div>

    </div>

</div>