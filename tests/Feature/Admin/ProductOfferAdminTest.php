<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOfferAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_it_persists_sale_price_from_the_product_admin_form(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Vestido Oferta',
            'category_id' => $category->id,
            'size_type' => Product::DEFAULT_SIZE_TYPE,
            'variations' => [
                [
                    'color' => 'Negro',
                    'size' => 'M',
                    'price' => 15000,
                    'sale_price' => 12000,
                    'stock' => 4,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $variation = Product::where('name', 'Vestido Oferta')
            ->firstOrFail()
            ->variations()
            ->firstOrFail();

        $this->assertSame(12000.0, (float) $variation->sale_price);
    }

    public function test_it_rejects_zero_or_greater_sale_price_values(): void
    {
        $category = Category::factory()->create();

        $response = $this->from(route('admin.products.create'))->post(route('admin.products.store'), [
            'name' => 'Vestido Invalido',
            'category_id' => $category->id,
            'size_type' => Product::DEFAULT_SIZE_TYPE,
            'variations' => [
                [
                    'color' => 'Negro',
                    'size' => 'M',
                    'price' => 15000,
                    'sale_price' => 15000,
                    'stock' => 4,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.products.create'));
        $response->assertSessionHasErrors('variations.0.sale_price');
    }
}
