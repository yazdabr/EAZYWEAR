<div
    x-data="uploadGallery()"
    class="space-y-5">

    {{-- Hidden Input --}}
    <input
        x-ref="fileInput"
        type="file"
        multiple
        accept="image/png,image/jpeg,image/webp"
        class="hidden"
        @change="handleFiles($event.target.files)">

    {{-- Upload Area --}}
    <div

        @click="$refs.fileInput.click()"

        @dragover.prevent="drag=true"

        @dragleave.prevent="drag=false"

        @drop.prevent="
            drag=false;
            handleFiles($event.dataTransfer.files)
        "

        :class="drag
            ? 'border-[#AE7C18] bg-[#AE7C18]/5'
            : 'border-slate-300 bg-slate-50'"

        class="cursor-pointer rounded-2xl border-2 border-dashed p-10 transition duration-300">

        <div
            class="flex flex-col items-center">

            <div
                class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">

                <x-heroicon-o-cloud-arrow-up
                    class="h-8 w-8 text-slate-500"/>

            </div>

            <h4
                class="font-semibold text-slate-800">

                Click or drag to upload

            </h4>

            <p
                class="mt-2 text-sm text-slate-500">

                JPG, PNG, WEBP (Max 5 MB)

            </p>

        </div>

    </div>

    {{-- Preview --}}
    <template
        x-if="images.length">

        <div
            class="grid grid-cols-4 gap-3">

            <template
                x-for="(image,index) in images"
                :key="index">

                <div
                    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white">

                    <img

                        :src="image"

                        class="aspect-square w-full object-cover">

                    <button

                        @click.stop="remove(index)"

                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-red-500 text-white opacity-0 transition group-hover:opacity-100">

                        <x-heroicon-o-x-mark
                            class="h-4 w-4"/>

                    </button>

                </div>

            </template>

            {{-- Add More --}}
            <button

                x-show="images.length<5"

                @click="$refs.fileInput.click()"

                class="flex aspect-square items-center justify-center rounded-xl border-2 border-dashed border-slate-300 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">

                <x-heroicon-o-plus
                    class="h-8 w-8 text-slate-400"/>

            </button>

        </div>

    </template>

</div>

<script>

function uploadGallery(){

    return{

        drag:false,

        images:[],

        init(){

            window.addEventListener('product-gallery-update',(event)=>{

                this.images = event.detail.images || [];

            });

        },

        handleFiles(files){

            [...files].forEach(file=>{

                if(this.images.length>=5) return;

                const reader=new FileReader();

                reader.onload=(e)=>{

                    this.images.push(e.target.result);

                };

                reader.readAsDataURL(file);

            });

        },

        remove(index){

            this.images.splice(index,1);

        }

    }

}

</script>