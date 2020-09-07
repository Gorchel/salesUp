<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function getLogs() {
        $logStr = file_get_contents(storage_path('logs/lumen-2020-09-07.log'));
        dd($logStr);
    }

    public function webhook(Request $request) {
        Log::info(json_encode($request->all()));
    }
}
