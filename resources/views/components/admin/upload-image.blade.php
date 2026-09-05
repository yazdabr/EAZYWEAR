@props(['name'=>'image'])
<div x-data="{
    items:[],
    error:'',
    maxFiles:5,
    init(){
        window.addEventListener('product-gallery-update',event=>{
            const images=event.detail.images||[];
            this.items=images.slice(0,this.maxFiles).map((image,index)=>{
                if(typeof image==='object'){
                    return {id:image.id??null,preview:image.url??image.preview??'',name:image.name??('Gambar '+(index+1)),existing:true};
                }
                return {id:null,preview:image,name:'Gambar '+(index+1),existing:true};
            });
            this.error='';
            if(this.$refs.fileInput)this.$refs.fileInput.value='';
        });
    },
    handleFile(event){
        this.error='';
        const files=Array.from(event.target.files||[]);
        if(!files.length)return;
        const remaining=this.maxFiles-this.items.length;
        if(files.length>remaining){
        this.error=`Maksimal ${this.maxFiles} foto produk. Kamu masih bisa menambahkan ${remaining} foto.`;
        event.target.value='';
        window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',title:'Upload Gagal',message:`Maksimal ${this.maxFiles} foto produk.`}}));
        return;
        }
        const maxSize=10*1024*1024;
        const invalidFile=files.find(file=>file.size>maxSize);
        if(invalidFile){
        this.error='Ukuran setiap foto maksimal 10 MB.';
        event.target.value='';
        window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',title:'Upload Gagal',message:'Ukuran setiap foto maksimal 10 MB.'}}));
        return;
        }
        const readers=files.map(file=>new Promise((resolve,reject)=>{
        const reader=new FileReader();
        reader.onload=e=>resolve({preview:e.target.result,name:file.name,file,existing:false});
        reader.onerror=()=>reject();
        reader.readAsDataURL(file);
        }));
        Promise.all(readers).then(results=>{
        this.items=[...this.items,...results];
        const dataTransfer=new DataTransfer();
        this.items.filter(item=>!item.existing&&item.file).forEach(item=>dataTransfer.items.add(item.file));
        this.$refs.fileInput.files=dataTransfer.files;
        this.$refs.fileInput.value='';
        }).catch(()=>{
        this.error='Gagal membaca file gambar.';
        event.target.value='';
        });
        },
        removeImage(index){
        this.items.splice(index,1);
        const input=this.$refs.fileInput;
        if(input){
        const dataTransfer=new DataTransfer();
        this.items.filter(item=>!item.existing&&item.file).forEach(item=>dataTransfer.items.add(item.file));
        input.files=dataTransfer.files;
        }
    },
    removeImage(index){
        const item=this.items[index];
        this.items.splice(index,1);
        if(!item?.existing){
            const newFileIndex=this.items.filter((item,index)=>!item.existing&&index<index).length;
        }
        const input=this.$refs.fileInput;
        if(input){
            const files=Array.from(input.files||[]);
            const newItems=this.items.filter(item=>!item.existing);
            const dataTransfer=new DataTransfer();
            files.slice(0,newItems.length).forEach(file=>dataTransfer.items.add(file));
            input.files=dataTransfer.files;
        }
    },
    clearImages(){
        this.items=[];
        this.error='';
        if(this.$refs.fileInput)this.$refs.fileInput.value='';
    },
    openFilePicker(){
        if(this.items.length>=this.maxFiles){
            this.error='Maksimal 5 foto produk.';
            return;
        }
        this.$refs.fileInput.click();
    },
    existingIds(){
        return this.items.filter(item=>item.existing&&item.id).map(item=>item.id);
    }
}" class="w-full">
    <input x-ref="fileInput" type="file" name="{{ $name }}[]" multiple accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFile($event)">
    <template x-for="id in existingIds()" :key="'existing-'+id">
        <input type="hidden" name="existing_images[]" :value="id">
    </template>
    <div x-show="items.length===0" class="relative flex min-h-[140px] sm:min-h-[220px] cursor-pointer flex-col items-center justify-center rounded-xl sm:rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-4 sm:p-8 text-center transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5" @click="openFilePicker()">
        <div class="mb-2 sm:mb-4 flex h-10 w-10 sm:h-14 sm:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-[#AE7C18]/10">
            <x-heroicon-o-cloud-arrow-up class="h-5 w-5 sm:h-7 sm:w-7 text-[#AE7C18]" />
        </div>
        <p class="text-xs sm:text-sm font-semibold text-slate-700">Klik untuk memilih gambar</p>
        <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-xs text-slate-400">PNG, JPG, atau WEBP</p>
        <p class="mt-0.5 sm:mt-1 text-[10px] sm:text-xs text-slate-400">Maksimal 5 foto • 10 MB/foto</p>
    </div>
    <template x-if="error">
        <div class="mt-2 sm:mt-3 flex items-start gap-1.5 sm:gap-2 rounded-lg sm:rounded-xl border border-red-200 bg-red-50 p-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-red-600">
            <x-heroicon-o-exclamation-circle class="mt-0.5 h-4 w-4 sm:h-5 sm:w-5 shrink-0" />
            <span x-text="error"></span>
        </div>
    </template>
    <div x-show="items.length>0" x-cloak class="space-y-2.5 sm:space-y-3">
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <template x-for="(item,index) in items" :key="(item.id?'existing-'+item.id:'new-'+index)">
                <div class="group relative overflow-hidden rounded-lg sm:rounded-xl border border-slate-200 bg-slate-100">
                    <img :src="item.preview" alt="Preview Produk" class="aspect-square w-full object-cover">
                    <div class="absolute inset-x-0 bottom-0 bg-black/60 px-1.5 py-1 sm:px-3 sm:py-2">
                        <p class="truncate text-[10px] sm:text-xs font-medium text-white" x-text="index===0?'Foto Utama':'Foto '+(index+1)"></p>
                    </div>
                    <button type="button" @click="removeImage(index)" class="absolute right-1 top-1 sm:right-2 sm:top-2 flex h-6 w-6 sm:h-8 sm:w-8 items-center justify-center rounded-md sm:rounded-lg bg-black/60 text-white transition hover:bg-red-500">
                        <x-heroicon-o-trash class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                    </button>
                </div>
            </template>
            <button x-show="items.length<maxFiles" type="button" @click="openFilePicker()" class="flex aspect-square flex-col items-center justify-center rounded-lg sm:rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 text-center transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">
                <div class="flex h-7 w-7 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-[#AE7C18]/10">
                    <x-heroicon-o-plus class="h-4 w-4 sm:h-5 sm:w-5 text-[#AE7C18]" />
                </div>
                <span class="mt-1 sm:mt-2 text-[10px] sm:text-xs font-semibold text-slate-600">Tambah</span>
                <span class="mt-0.5 text-[9px] sm:text-[10px] text-slate-400" x-text="(maxFiles-items.length)+' slot'"></span>
            </button>
        </div>
        <div class="flex items-center justify-between rounded-lg sm:rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 sm:px-4 sm:py-3">
            <div>
                <p class="text-xs sm:text-sm font-semibold text-slate-700"><span x-text="items.length"></span>/5 Foto</p>
                <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">Foto pertama adalah foto utama.</p>
            </div>
            <button type="button" @click="clearImages()" class="text-xs font-semibold text-red-500 transition hover:text-red-700">Hapus Semua</button>
        </div>
    </div>
</div>