<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;

/**
 * Class WebhookController
 * @package App\Http\Controllers
 */
class WebhookController extends Controller
{
    /**
     * Просмотр логов
     * @param Request $request
     */
    public function getLogs(Request $request)
    {
        $logStr = file_get_contents(storage_path('logs/lumen-'.$request->get('date').'.log'));
        dd($logStr);
    }

    /**
     * Обновление Контактов
     * @param Request $request
     * @return int|void
     */
    public function webhook(Request $request)
    {
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

    /**
     * Получение яндекс карты
     * @param Request $request
     */
    public function webhookObjects(Request $request)
    {
        Log::info(json_encode($request->all()));

        $id = $request->get('ids')[0];
        $token = $request->get('token');
        $type = $request->get('type');

        $data = [
            'token' => $token,
            'id' => $id,
            'type' => $type,
        ];

        if (!empty($id)) {
            $salesupHandler = new SalesupHandler($token);
            $response = $salesupHandler->getObjects($id);
            $attribute = $response['attributes'];

            if (!empty($attribute['longitude'])) {
                $data['longitude'] = $attribute['longitude'];
            }

            if (!empty($attribute['latitude'])) {
                $data['latitude'] = $attribute['latitude'];
            }
        }

        return view('objects.ya', $data);
    }
}
