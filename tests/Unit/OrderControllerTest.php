<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\OrderController;
use App\Jobs\OrderJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_success_with_default_parameters()
    {
        Order::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'recipe_name', 'ingredients', 'status', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_index_success_with_custom_parameters()
    {
        Order::factory()->count(20)->create([
            'status' => 'pending',
        ]);
        Order::factory()->count(5)->create([
            'status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/orders?take=5&pending_orders=1&order_direction=ASC');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'recipe_name', 'ingredients', 'status', 'created_at', 'updated_at'],
                ],
            ])->assertJsonCount(5, 'data');
    }

    public function test_store_success()
    {
        Queue::fake();

        Http::fake([
            env('MS_RANDOM_RECIPES_URL') => Http::response([
                'data' => [
                    'name' => 'Test Recipe',
                    'ingredients' => [
                        [
                            "ingredient"=>"tomato",
                            "quantity"=>1
                        ]
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['message']]);

        $this->assertDatabaseHas('orders', [
            'recipe_name' => 'Test Recipe',
        ]);

        Queue::assertPushed(OrderJob::class);
    }

    public function test_store_failed_external_api_error()
    {
        Http::fake([
            env('MS_RANDOM_RECIPES_URL') => Http::response([], 400),
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(400)
            ->assertJsonStructure(['message']);
    }

    public function test_store_exception_handling()
    {
        Http::fake([
            env('MS_RANDOM_RECIPES_URL') => Http::response([
                'data' => [
                    'name' => 'Test Recipe',
                    'ingredients' => 'Test Ingredients',
                ],
            ], 200),
        ]);
        $this->mock(Order::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(\Exception::class);
        });

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(500)
            ->assertJsonStructure(['message']);
    }
}
