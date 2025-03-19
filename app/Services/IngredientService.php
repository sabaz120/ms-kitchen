<?php

namespace App\Services;
use App\Models\{
    Inventory,
    Purchase
};
use Illuminate\Support\Facades\Http;
use Exception;
class IngredientService
{

    public function buyIngredients(string $ingredient)
    {
        try{
            $urlMarket = env('INGREDIENTS_MARKET_URL','');
            $response = Http::get($urlMarket.'?ingredient='.$ingredient);
    
            if ($response->successful()) {
                $quantitySold = $response->json('quantitySold');
                if ($quantitySold > 0) {
                    Inventory::where('ingredient', $ingredient)->increment('quantity', $quantitySold);
                    Purchase::create([
                        'ingredient' => $ingredient,
                        'quantity' => $quantitySold,
                    ]);
                    // \Log::info([
                    //     'ingredient' => $ingredient,
                    //     'quantity' => $quantitySold,
                    // ]);
                    return (object)[
                        "status"=>true
                    ];
                } else {
                    throw new Exception('No hay suficiente stock para comprar el ingrediente');
                }
            } else {
                throw new Exception('Error al intentar comprar el ingrediente');
            }
        }catch(Exception $e){
            return (object)[
                "status"=>false,
            ];
        }
    }

}