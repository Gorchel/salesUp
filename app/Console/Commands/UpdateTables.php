<?php
namespace App\Console\Commands;
use App\Classes\SalesUp\SalesupHandler;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Orders;
use App\Properties;

/**
 * Class UpdateTables
 * @package App\Console\Commands
 */
class UpdateTables extends Command
{
    /**
     *
     */
    const COUNT_PER_PAGE = 100;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_tables:init {dayUpdated?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет таблицу';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $handler = new SalesupHandler(env('API_TOKEN'));
        $methods = $handler->methods;

        $dayUpdated = $this->argument('dayUpdated');
        $filters = [];

        if (!empty($dayUpdated)) {
            $now = Carbon::now('Africa/Nairobi');
            $now->subDays($dayUpdated);

            $filters['updated-at-gte'] = $now->startOfDay()->format('Y.m.d H:s');
        }

        //Получаем Список заявок
        $ordersData = $methods->getOrders(1, self::COUNT_PER_PAGE, $filters);

        if (empty($ordersData['data'])) {
            return;
        }

        $this->eachOrders($ordersData['data']);

        $pageNumber = $ordersData['meta']['page-count'];

        if ($pageNumber > 1) {
            for ($page = 2; $page<=$pageNumber; $page++) {
                $ordersData = $methods->getOrders($page, self::COUNT_PER_PAGE, $filters);
                $this->eachOrders($ordersData['data']);
            }
        }

        //Получаем Список недвижки
        $propertyData = $methods->getPaginationObjects(1, self::COUNT_PER_PAGE, $filters);

        if (empty($propertyData['data'])) {
            return;
        }

        $this->eachProperties($propertyData['data']);

        $pageNumber = $propertyData['meta']['page-count'];

        if ($pageNumber > 1) {
            for ($page = 2; $page<=$pageNumber; $page++) {
                $propertyData = $methods->getPaginationObjects($page, self::COUNT_PER_PAGE, $filters);
                $this->eachProperties($propertyData['data']);
            }
        }
    }

    /**
     * @param $orders
     */
    protected function eachOrders($orders)
    {
        if (!empty($orders)) {
            foreach ($orders as $orderKey => $order) {
                $this->storeOrders($order);
            }
        }
    }

    /**
     * @param $properties
     */
    protected function eachProperties($properties)
    {
        if (!empty($properties)) {
            foreach ($properties as $orderKey => $property) {
                $this->storeProperty($property);
            }
        }
    }

    /**
     * @param $order
     */
    public function storeOrders($order)
    {
        $orderModel = Orders::where('id', $order['id'])
            ->first();

        $attributes = $order['attributes'];

        if (empty($orderModel)) {
            $orderModel = new Orders;
            $orderModel->id = $order['id'];
            $orderModel->created_at = $attributes['created-at'];
        } else {
            if (!empty($attributes['discarded-at'])) {
                $orderModel->delete();
                return;
            }

            if ($orderModel->updated_at == $attributes['updated-at']) {
                return;
            }
        }

        $orderModel->updated_at = $attributes['updated-at'];
        $orderModel->customs = json_encode($attributes['customs']);
        $orderModel->type = $this->getType($attributes['customs']['custom-67821']);

        unset($attributes['customs']);

        $orderModel->attributes = json_encode($attributes);

        $relationships = [
            'contacts' => $order['relationships']['contacts'],
            'companies' => $order['relationships']['companies'],
        ];

        $orderModel->relationships = json_encode($relationships);
        $orderModel->save();
    }

    protected function getType($str)
    {
        switch($str) {
            case 'Сдам':
                return 1;
            case 'Продам':
                return 2;
            case 'Куплю':
                return 3;
            default:
                return 4;
        }
    }

    /**
     * @param $property
     */
    public function storeProperty($property)
    {
        $propertyModel = Properties::where('id', $property['id'])
            ->first();

        $attributes = $property['attributes'];
        $now = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');

        if (empty($propertyModel)) {
            $propertyModel = new Properties;
            $propertyModel->id = $property['id'];
            $propertyModel->created_at = $now;
        } else {
            if (!empty($attributes['discarded-at'])) {
                $propertyModel->delete();
                return;
            }

//            $updatedAt = Carbon::parse($attributes['updated-at'])->format('Y-m-d H:i:s');

            if ($propertyModel->updated_at == $now) {
                return;
            }
        }

        $propertyModel->updated_at = $now;
        $propertyModel->customs = json_encode($attributes['customs']);

        unset($attributes['customs']);

        $propertyModel->attributes = json_encode($attributes);

        $relationships = [
            'contacts' => $property['relationships']['contacts'],
            'companies' => $property['relationships']['companies'],
        ];

        $propertyModel->relationships = json_encode($relationships);
        $propertyModel->save();
    }
}
