<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShopRouteTest extends TestCase
{
    public function test_shop_page_is_accessible(): void
    {
        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertViewIs('pages.shop');
    }

    public function test_catalog_redirects_to_shop_and_preserves_query_string(): void
    {
        $response = $this->get('/catalog?search=jersey');

        $response->assertRedirect('/shop?search=jersey');
        $response->assertStatus(301);
    }

    public function test_legacy_catalog_product_url_redirects_to_shop_product_url(): void
    {
        $response = $this->get('/catalog/product/test-jersey?size=M');

        $response->assertRedirect('/shop/product/test-jersey?size=M');
        $response->assertStatus(301);
    }
}