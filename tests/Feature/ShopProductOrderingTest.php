<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopProductOrderingTest extends TestCase
{
    use DatabaseTransactions;

    private string $searchToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchToken = 'ZZSHOP' . strtoupper(uniqid());
    }

    private function category(string $name): Category
    {
        return Category::query()->firstOrCreate(
            ['name' => $name],
            [
                'slug' => Str::slug($name . '-' . uniqid()),
                'status' => true,
            ]
        );
    }

    private function createProduct(string $name, Category $category): Product
    {
        return Product::query()->create([
            'product_code' => 'TST-' . strtoupper(uniqid()),
            'category_id' => $category->id,
            'name' => $this->searchToken . ' ' . $name,
            'slug' => Str::slug($this->searchToken . '-' . $name . '-' . uniqid()),
            'availability' => 'ready',
            'status' => true,
        ]);
    }

    public function test_shop_orders_stadium_then_supporter_then_polo_then_other_products(): void
    {
        $jersey = $this->category('Jersey');
        $polo = $this->category('Polo');

        // Create in a scrambled order so the default latest-first ordering cannot pass.
        $other = $this->createProduct('Training Tee', $jersey);
        $poloProduct = $this->createProduct('Polo Shirt Grey', $polo);
        $supporterThird = $this->createProduct('Supporter Version 2099/00 - Third', $jersey);
        $supporterAway = $this->createProduct('Supporter Version 2099/00 - Away', $jersey);
        $supporterHome = $this->createProduct('Supporter Version 2099/00 - Home', $jersey);
        $stadiumThird = $this->createProduct('Stadium Version 2099/00 - Third', $jersey);
        $stadiumAway = $this->createProduct('Stadium Version 2099/00 - Away', $jersey);
        $stadiumHome = $this->createProduct('Stadium Version 2099/00 - Home', $jersey);

        $response = $this->get('/shop?' . http_build_query([
            'search' => $this->searchToken,
        ]));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertSame([
            $stadiumHome->id,
            $stadiumAway->id,
            $stadiumThird->id,
            $supporterHome->id,
            $supporterAway->id,
            $supporterThird->id,
            $poloProduct->id,
            $other->id,
        ], $products->pluck('id')->all());
    }

    public function test_shop_ordering_stays_consistent_across_pagination(): void
    {
        $jersey = $this->category('Jersey');

        $stadiumHome = $this->createProduct('Stadium Version 2099/00 - Home', $jersey);
        $stadiumAway = $this->createProduct('Stadium Version 2099/00 - Away', $jersey);
        $stadiumThird = $this->createProduct('Stadium Version 2099/00 - Third', $jersey);
        $supporterHome = $this->createProduct('Supporter Version 2099/00 - Home', $jersey);
        $supporterAway = $this->createProduct('Supporter Version 2099/00 - Away', $jersey);
        $supporterThird = $this->createProduct('Supporter Version 2099/00 - Third', $jersey);

        $otherProducts = collect(range(1, 8))
            ->map(fn (int $index) => $this->createProduct('Training Tee ' . $index, $jersey));

        $firstPage = $this->get('/shop?' . http_build_query([
            'search' => $this->searchToken,
            'page' => 1,
        ]));

        $secondPage = $this->get('/shop?' . http_build_query([
            'search' => $this->searchToken,
            'page' => 2,
        ]));

        $firstPage->assertOk();
        $secondPage->assertOk();

        $firstPageIds = $firstPage->viewData('products')->pluck('id')->all();
        $secondPageIds = $secondPage->viewData('products')->pluck('id')->all();

        $this->assertSame([
            $stadiumHome->id,
            $stadiumAway->id,
            $stadiumThird->id,
            $supporterHome->id,
            $supporterAway->id,
            $supporterThird->id,
        ], array_slice($firstPageIds, 0, 6));

        $this->assertCount(2, $secondPageIds);
        $this->assertEmpty(array_diff($secondPageIds, $otherProducts->pluck('id')->all()));

        $combinedIds = array_merge($firstPageIds, $secondPageIds);
        $expectedIds = array_merge(
            [
                $stadiumHome->id,
                $stadiumAway->id,
                $stadiumThird->id,
                $supporterHome->id,
                $supporterAway->id,
                $supporterThird->id,
            ],
            $otherProducts->pluck('id')->all()
        );

        sort($combinedIds);
        sort($expectedIds);

        $this->assertSame($expectedIds, $combinedIds);
    }
}
