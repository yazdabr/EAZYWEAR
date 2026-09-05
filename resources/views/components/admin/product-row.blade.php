@props(['product'])

@php
$thumbnail=$product->images->where('is_thumbnail',true)->first();
$firstImage=$product->images->first();
$imagePath=$thumbnail?->image??$firstImage?->image;
if($imagePath){
    $image=(str_starts_with($imagePath,'http://')||str_starts_with($imagePath,'https://'))
        ?$imagePath
        :((str_starts_with($imagePath,'images/')||str_starts_with($imagePath,'storage/'))
            ?asset($imagePath)
            :asset('storage/'.$imagePath));
}else{
    $image=asset('images/products/1.png');
}
$galleryImages=$product->images->sortBy('sort_order')->take(5)->map(function($galleryImage){
    $path=$galleryImage->image;
    $url=(str_starts_with($path,'http://')||str_starts_with($path,'https://'))?$path:((str_starts_with($path,'images/')||str_starts_with($path,'storage/'))?asset($path):asset('storage/'.$path));
    return ['id'=>$galleryImage->id,'url'=>$url];
})->values()->all();

$category=$product->category;

if($category instanceof \App\Models\Category){
    $categoryName=$category->name;
}elseif(is_array($category)){
    $categoryName=$category['name']??'-';
}elseif(is_string($category)){
    $decodedCategory=json_decode($category,true);
    $categoryName=(json_last_error()===JSON_ERROR_NONE&&is_array($decodedCategory))
        ?($decodedCategory['name']??'-')
        :$category;
}else{
    $categoryName='-';
}

$variantsData=[];
$sizeIds=[];

foreach($product->variants as $variant){
    if(!$variant->size_id){
        continue;
    }

    $sizeId=(string)$variant->size_id;

    $sizeIds[]=(int)$variant->size_id;

    $variantsData[$sizeId]=[
        'id'=>$variant->id,
        'size_id'=>(int)$variant->size_id,
        'name'=>$variant->size?->name??'',
        'price'=>(int)($variant->price??0),
        'stock'=>(int)($variant->inventory?->stock??0),
    ];
}

$sizeIds=array_values(array_unique($sizeIds));

$firstVariant=$product->variants->first();
$price=$firstVariant?->price??0;
$stock=$product->variants->sum(fn($v)=>(int)($v->inventory?->stock??$v->stock??0));
$status=$product->status?'Aktif':'Tidak Aktif';
$updated=$product->updated_at?$product->updated_at->diffForHumans():'-';

$editData=[
    'id'=>$product->id,
    'name'=>$product->name,
    'category_id'=>$product->category_id,
    'product_code'=>$product->product_code,
    'description'=>$product->description,
    'material'=>$product->material,
    'price'=>(int)$price,
    'stock'=>(int)$stock,
    'status'=>(bool)$product->status,
    'image'=>$image,
    'images'=>$galleryImages,
    'size_ids'=>$sizeIds,
    'variants'=>$variantsData,
];

$viewData=[
    'id'=>$product->id,
    'image'=>$image,
    'images'=>$galleryImages,
    'name'=>$product->name,
    'category'=>$categoryName,
    'category_id'=>$product->category_id,
    'product_code'=>$product->product_code,
    'description'=>$product->description,
    'material'=>$product->material,
    'price'=>(int)$price,
    'stock'=>(int)$stock,
    'status'=>$status,
    'updated'=>$updated,
    'size_ids'=>$sizeIds,
    'variants'=>$variantsData,
];
@endphp

<tr data-product-id="{{ $product->id }}" class="transition duration-200 hover:bg-slate-50">
    <td class="px-6 py-5">
        <div class="flex items-center gap-4">
            <img src="{{ $image }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-xl border border-slate-200 object-cover">
            <div>
                <h3 class="font-semibold text-slate-900">{{ $product->name }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $product->material?:'Jersey Kustom Premium' }}</p>
            </div>
        </div>
    </td>

    <td class="px-6 py-5">
        <div>
            <p class="font-medium text-slate-900">{{ $product->product_code?:'-' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $categoryName }}</p>
        </div>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="font-bold text-[#AE7C18]">Rp {{ number_format($price,0,',','.') }}</span>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ $stock }}</span>
    </td>

    <td class="px-6 py-5 text-center">
        <x-admin.badge-status status="{{ $status }}" />
    </td>

    <td class="px-6 py-5 text-center">
        <span class="text-sm text-slate-500">{{ $updated }}</span>
    </td>

    <td class="px-6 py-5 text-center">
        <div x-data="{open:false,topPos:0,leftPos:0,dropUp:false,toggle(){if(!this.open){const rect=this.$refs.btn.getBoundingClientRect();const menuHeight=150;this.dropUp=(window.innerHeight-rect.bottom)<menuHeight&&rect.top>menuHeight;this.topPos=this.dropUp?rect.top-menuHeight-6:rect.bottom+6;this.leftPos=rect.right-176}this.open=!this.open}}" class="relative inline-block text-left">
            <button x-ref="btn" type="button" @click="toggle()" title="Aksi" class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20" :class="open?'bg-slate-100':''">
                <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
            </button>

            <template x-teleport="body">
                <div x-show="open" @click.outside="open=false" @scroll.window="open=false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" :style="`top:${topPos}px;left:${leftPos}px;`" class="fixed z-[9999] w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-900/10" style="display:none;">

                {{-- LIHAT --}}
                <button type="button" @click.stop="open = false; window.dispatchEvent(new CustomEvent('open-view-product', {
                    detail:{
                    id:@js($product->id),
                    image:@js($image),
                    images:@js($galleryImages),
                    name:@js($product->name),
                    category:@js($categoryName),
                    category_id:@js($product->category_id),
                    product_code:@js($product->product_code),
                    description:@js($product->description),
                    material:@js($product->material),
                    price:@js($price),
                    stock:@js($stock),
                    status:@js($status),
                    updated:@js($updated),
                    size_ids:@js($sizeIds),
                    variants:@js($variantsData)
                    }
                }))" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500" />
                    <span>Lihat</span>
                </button>

                <button
                    type="button"
                    @click.stop="
                        open=false;
                        window.dispatchEvent(new CustomEvent('open-edit-product',{
                            detail:@js($editData)
                        }));
                    "
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    <x-heroicon-o-pencil-square class="h-4 w-4 shrink-0 text-slate-500"/>
                    <span>Ubah</span>
                </button>

                    <button type="button" @click="open=false;window.dispatchEvent(new CustomEvent('open-delete-product',{detail:{id:@js($product->id),name:@js($product->name)}}))" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <x-heroicon-o-trash class="h-4 w-4 shrink-0"/>
                        <span>Hapus</span>
                    </button>
                </div>
            </template>
        </div>
    </td>
</tr>