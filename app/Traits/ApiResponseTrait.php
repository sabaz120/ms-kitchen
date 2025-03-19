<?php

namespace App\Traits;

use Exception;
use Throwable;

/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, int $code = 200, string $message = 'Peticion exitosa, todo salio bien!',array $appendData = null)
	{
        $response = [
            'data' => $data,
            'message' => $message,
            'success' => true
        ];

        if ($appendData && is_array($appendData)) {
            $response = array_merge($response,$appendData);
        }

		return response()->json($response, $code);
	}

    /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function pagination($data, string $key = 'data', string $message = 'No hay registros que coincidan en nuestra base de datos.', int $code = 200)
	{
        if(is_array($data)){
            $appendData = $data['append'];
            $data = $data['data'];
        }

        $dataResponse = collect($data)->only('data')['data'];

        if(count($dataResponse) < 1){
            $response = [
                'status' => 'Success',
                'message' => $message,
                'data' => []
            ];
        }else{
            $response = [
                'success' => 'Success',
                $key => $dataResponse
            ];

            if (!empty($appendData)) {
                foreach ($appendData as $key => $content) {
                    $response[$key] = $content;
                }
            }

            $response['pagination'] = collect($data)->except('data');
        }

		return response()->json($response, $code);
	}

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function error(string $message = 'Ocurrió un error en la petición', int $code = 400, $data = null)
	{
        $return = [
            'success' => false,
			'message' => $message,
            'data' => $data ?? []
        ];

        return response()->json($return, $code);
	}
}