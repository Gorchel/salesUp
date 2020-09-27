<?php

namespace App\Http\Controllers;

use App\Classes\SalesUp\SalesupMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;

/**
 * Class TestController
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    public function index(Request $request) {
        $methods = new SalesupMethods($request->get('token'));
        dd($methods->getOrders());
    }
}
