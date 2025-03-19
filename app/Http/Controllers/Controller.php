<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
//Traits
use App\Traits\ApiResponseTrait;
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests,ApiResponseTrait;
}
