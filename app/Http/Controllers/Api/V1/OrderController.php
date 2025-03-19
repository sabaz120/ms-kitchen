<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{
    Order,
};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Jobs\OrderJob;
use Validator;
use Illuminate\Support\Facades\Http;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $take=$request->input('take',10);
            $pending_orders=$request->input('pending_orders',null);
            $status=$request->input('status',null);
            $orderDirection=$request->input('order_direction')??'DESC';
            $orderDirection = in_array($orderDirection, ['ASC', 'DESC']) 
                ? $orderDirection
                : 'ASC';
            $query=Order::query();
            if($status){
                $query->where("status",$status);
            }
            if($pending_orders){
                $query->whereIn('status',[
                    "pending",
                    "preparing"
                ]);
            }
            $query->orderBy("updated_at",$orderDirection);
            $query=$query->paginate($take);
            return $this->pagination($query);
        } catch (Exception $e) {
            \Log::error([
                "message"=>$e->getMessage(),
                "line"=>$e->getLine(),
            ]);
            return $this->error('Ocurrió un error al intentar obtener el listado', 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $randomRecipesUrl=env('MS_RANDOM_RECIPES_URL');
            $response = Http::get($randomRecipesUrl);
            if($response->successful()){
                $result=$response->json()['data'];
                $order=Order::create([
                    'recipe_name'=>$result['name'],
                    'ingredients'=>$result['ingredients'],
                ]);
                OrderJob::dispatch($order->id);
                return $this->success(['message'=>'Pedido creado correctamente, receta: '.$result['name']]);
            }else{
                return $this->error('Ocurrió un error al intentar obtener una receta', 400);
            }
        }catch(Exception $e){
            \Log::error([
                "message"=>$e->getMessage(),
                "file"=>$e->getFile(),
                "line"=>$e->getLine(),
            ]);
            return $this->error('Ocurrió un error al intentar procesar la solicitud', 500);
        }
    }
}