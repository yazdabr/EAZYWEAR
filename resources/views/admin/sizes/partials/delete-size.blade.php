<div
    x-data="{

        open:false,

        size:{

            size:'',

            description:''

        },

        openDelete(size){

            this.size = size;

            this.open = true;

        }

    }"

    x-on:open-delete-size.window="openDelete($event.detail)">

    {{-- Overlay --}}
    <div

        x-show="open"

        x-transition.opacity

        @click="open=false"

        class="fixed inset-0 z-[200] bg-black/40 backdrop-blur-sm"

        style="display:none;">

    </div>

    {{-- Modal --}}
    <div

        x-show="open"

        x-transition:enter="transition duration-300"

        x-transition:enter-start="opacity-0 scale-90"

        x-transition:enter-end="opacity-100 scale-100"

        x-transition:leave="transition duration-200"

        x-transition:leave-start="opacity-100 scale-100"

        x-transition:leave-end="opacity-0 scale-90"

        class="fixed inset-0 z-[201] flex items-center justify-center p-5"

        style="display:none;">

        <div

            @click.outside="open=false"

            class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">

            {{-- Icon --}}
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100">

                <x-heroicon-o-trash
                    class="h-10 w-10 text-red-600"/>

            </div>

            {{-- Judul --}}
            <h2
                class="mt-6 text-center text-2xl font-bold text-slate-900">

                Hapus Ukuran?

            </h2>

            {{-- Deskripsi --}}
            <p
                class="mt-3 text-center text-slate-500">

                Apakah Anda yakin ingin menghapus

                <span

                    class="font-semibold text-slate-900"

                    x-text="size.size">

                </span>

                ?

            </p>

            <p
                class="mt-2 text-center text-sm text-red-500">

                Tindakan ini tidak dapat dibatalkan.

            </p>

            {{-- Tombol --}}
            <div
                class="mt-8 flex gap-3">

                <button

                    @click="open=false"

                    class="flex-1 rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                    Batal

                </button>

                <button

                    @click="

                        open=false;

                        setTimeout(()=>{

                            $dispatch('toast',{

                                type:'error',

                                title:'Ukuran Dihapus',

                                message:'Ukuran telah berhasil dihapus.'

                            });

                        },300);

                    "

                    class="flex-1 rounded-xl bg-red-600 px-5 py-3 font-semibold text-white shadow-lg shadow-red-500/20 transition hover:bg-red-700">

                    Hapus

                </button>

            </div>

        </div>

    </div>

</div>