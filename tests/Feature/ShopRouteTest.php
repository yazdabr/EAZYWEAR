<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShopRouteTest extends TestCase
{
    public function test_shop_page_is_accessible(): void
    {
        $response = $this->get('/shop');

        $response->assertOk();
    }

    public function test_old_catalog_url_redirects_permanently_to_shop(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(301);
        $response->assertRedirect('/shop');
    }

    public function test_old_catalog_product_url_redirects_to_new_shop_product_url(): void
    {
        $response = $this->get('/catalog/product/test-product');

        $response->assertStatus(301);
        $response->assertRedirect('/shop/product/test-product');
    }
}
