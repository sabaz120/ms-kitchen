<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
class OrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $orderId;
    public function __construct($orderId)
    {
        $this->orderId=$orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order=Order::find($this->orderId);
        if($order->status=='pending'){
            $order->status='preparing';
            $order->update();
        }
        $ingredients = collect($order->ingredients, true)->map(function ($ingredient) {
            return [
            'name' => $ingredient['ingredient'],
            'quantity' => $ingredient['quantity'],
            ];
        })->toArray();
        $requestIngredientsUrl=env('MS_REQUEST_INGREDIENTS_URL');
        $response = Http::post($requestIngredientsUrl,[
            'ingredients'=>$ingredients
        ]);
        // \Log::info([
        //     'requestIngredientsUrl'=>$requestIngredientsUrl,
        //     'ingredients'=>$ingredients,
        //     'response'=>$response->json()
        // ]);
        if($response->failed()){
            self::dispatch($this->orderId)->delay(now()->addSeconds(10));
        }elseif($response->successful()){
            $order->status='completed';
            $order->update();
        }
    }
}
