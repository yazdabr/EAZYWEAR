<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MetaProductFeedController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->where('status', true)
            ->with([
                'images' => fn ($query) => $query->orderBy('sort_order'),
                'variants.size',
                'variants.color',
                'variants.inventory',
            ])
            ->orderBy('id')
            ->get();

        $escape = static fn ($value): string => htmlspecialchars(
            trim((string) $value),
            ENT_XML1 | ENT_COMPAT,
            'UTF-8'
        );

        $items = [];
        $baseUrl = rtrim(config('app.url'), '/');

        foreach ($products as $product) {
            $image = $product->images->firstWhere('is_thumbnail', true)
                ?? $product->images->first();

            if (!$image || !$image->image) {
                continue;
            }

            $imageUrl = Storage::disk('public')->url($image->image);

            if (!str_starts_with($imageUrl, 'http')) {
                $imageUrl = $baseUrl . '/' . ltrim($imageUrl, '/');
            }

            foreach ($product->variants as $variant) {
                $isAvailable = $product->availability === 'pre_order'
                    ? !$product->pre_order_closed
                    : (!$product->ready_stock_closed && (int) ($variant->inventory?->stock ?? 0) > 0);

                $title = $product->name;
                if ($variant->size?->name) {
                    $title .= ' - ' . $variant->size->name;
                }

                $description = strip_tags((string) ($product->description ?: $product->name));
                $description = preg_replace('/\s+/', ' ', $description);

                $fields = [
                    'g:id' => $variant->sku ?: ($product->product_code . '-' . $variant->id),
                    'g:title' => $title,
                    'g:description' => $description,
                    'g:availability' => $isAvailable ? 'in stock' : 'out of stock',
                    'g:condition' => 'new',
                    'g:price' => number_format((float) $variant->price, 2, '.', '') . ' IDR',
                    'g:link' => $baseUrl . route(
                        'product.detail',
                        ['product' => $product->slug],
                        false
                    ),
                    'g:image_link' => $imageUrl,
                    'g:brand' => 'Eazywear',
                    'g:item_group_id' => $product->product_code,
                    'g:google_product_category' => 'Apparel & Accessories > Clothing',
                    'g:fb_product_category' => 'Clothing & Accessories > Clothing',
                ];

                if ($variant->size?->name) {
                    $fields['g:size'] = $variant->size->name;
                }
                if ($variant->color?->name) {
                    $fields['g:color'] = $variant->color->name;
                }
                if ($product->material) {
                    $fields['g:material'] = $product->material;
                }

                $itemXml = "    <item>\n";
                foreach ($fields as $tag => $value) {
                    $itemXml .= "      <{$tag}>{$escape($value)}</{$tag}>\n";
                }
                $items[] = $itemXml . "    </item>";
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n"
            . "  <channel>\n"
            . '    <title>' . $escape(config('app.name', 'Eazywear')) . "</title>\n"
            . '    <link>' . $escape($baseUrl . '/shop') . "</link>\n"
            . "    <description>Product catalog Eazywear</description>\n"
            . implode("\n", $items) . "\n"
            . "  </channel>\n"
            . '</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
