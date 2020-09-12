<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;

class WebhookController extends Controller
{
    public function getLogs(Request $request)
    {
        $logStr = file_get_contents(storage_path('logs/lumen-'.$request->get('date').'.log'));
        dd($logStr);
    }

    public function webhook(Request $request) {
        //{"ids":["1678688"],"token":"Ph-AhX3_sc1GGkW2h6QLxiGxnH6DCBA8SnthhTRa6aA","type":"deals","user_id":"54273"}
        Log::info(json_encode($request->all()));

        if ($request->get('type') !== 'deals') {
            return;
        }

        $dealsIdsArrays = $request->get('ids');
        $token = $request->get('token');

        foreach ($dealsIdsArrays as $dealId) {
            $salesupHandler = new SalesupHandler($token);
            $response = $salesupHandler->updateDeals($dealId);
        }

        return 1;
    }
}
