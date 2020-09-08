<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function getLogs(Request $request) {
        $logStr = file_get_contents(storage_path('logs/lumen-'.$request->get('date').'.log'));
        dd($logStr);
    }

    public function webhook(Request $request) {
        Log::info(json_encode($request->all()));
    }
}
