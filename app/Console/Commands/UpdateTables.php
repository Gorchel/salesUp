<?php
namespace App\Console\Commands;
use App\Classes\SalesUp\SalesupHandler;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Orders;
use App\Properties;
use App\Company;

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

    const ORDERS_TYPE = 'orders';
    const PROPERTIES_TYPE = 'property';
    const COMPANY_TYPE = 'company';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_tables:init {type} {dayUpdated?}';

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
        \Log::info('run '.$this->argument('type').' '.$this->argument('dayUpdated'));
        $handler = new SalesupHandler(env('API_TOKEN'));
        $methods = $handler->methods;

        $dayUpdated = $this->argument('dayUpdated');

        \Log::info($dayUpdated);
        $type = $this->argument('type');
        \Log::info($type);
        $filters = [];

        if (!empty($dayUpdated)) {
            $now = Carbon::now('Africa/Nairobi');
            $now->subDays($dayUpdated);

            $filters['updated-at-gte'] = $now->startOfDay()->format('Y.m.d H:s');
        }

        if ($type == self::ORDERS_TYPE) {
            //Получаем Список заявок
            $ordersData = $methods->getOrders(1, self::COUNT_PER_PAGE, $filters);

            if (!empty($ordersData['data'])) {
                $this->eachOrders($ordersData['data']);

                $pageNumber = $ordersData['meta']['page-count'];

                if ($pageNumber > 1) {
                    for ($page = 2; $page<=$pageNumber; $page++) {
                        $ordersData = $methods->getOrders($page, self::COUNT_PER_PAGE, $filters);
                        $this->eachOrders($ordersData['data']);
                    }
                }
            }
        } else if($type == self::PROPERTIES_TYPE)  {
            //Получаем Список недвижки
            $propertyData = $methods->getPaginationObjects(1, self::COUNT_PER_PAGE, $filters);

            if (!empty($propertyData['data'])) {
                $this->eachProperties($propertyData['data']);

                $pageNumber = $propertyData['meta']['page-count'];

                if ($pageNumber > 1) {
                    for ($page = 2; $page<=$pageNumber; $page++) {
                        $propertyData = $methods->getPaginationObjects($page, self::COUNT_PER_PAGE, $filters);
                        $this->eachProperties($propertyData['data']);
                    }
                }
            }
        } else if($type == self::COMPANY_TYPE)  {
            //Получаем Список недвижки
            $companyData = $methods->getPaginationCompany(1, self::COUNT_PER_PAGE, $filters);

            if (!empty($companyData['data'])) {
                $this->eachCompany($companyData['data']);

                $pageNumber = $companyData['meta']['page-count'];

                if ($pageNumber > 1) {
                    for ($page = 2; $page<=$pageNumber; $page++) {
                        $companyData = $methods->getPaginationCompany($page, self::COUNT_PER_PAGE, $filters);
                        $this->eachCompany($companyData['data']);
                    }
                }
            }
        }

        return true;
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
     * @param $companies
     */
    protected function eachCompany($companies)
    {
        if (!empty($companies)) {
            foreach ($companies as $orderKey => $company) {
                $this->storeCompany($company);
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

        $now = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');

        if (!empty($attributes['discarded-at'])) {
            if (!empty($orderModel)) {
                $orderModel->delete();
            }

            return;
        }

        if (empty($orderModel)) {
            $orderModel = new Orders;
            $orderModel->id = $order['id'];
            $orderModel->created_at = $now;
        } else {
            if ($orderModel->updated_at == $now) {
                return;
            }
        }

        $orderModel->updated_at = $now;
        $orderModel->customs = json_encode($attributes['customs']);

        $type = array_values(array_diff(array_map('trim', $attributes['customs']['custom-67821']), ['']));

        if (isset($type[0])) {
            $orderModel->type = $this->getType($type[0]);
        }

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

        $attributes = $property['attributes'];

        $propertyModel = Properties::where('id', $property['id'])
            ->first();


        $now = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');

        if (!isset($attributes['customs']['custom-71235'][0]) || !in_array(
            $attributes['customs']['custom-71235'][0], ['Горящий', 'Активный','Горящий VIP','Активный VIP']
            )) {
            if (!empty($propertyModel)) {
                $propertyModel->delete();
            }

            return;
        }



        if (!empty($attributes['discarded-at'])) {
            if (!empty($propertyModel)) {
                $propertyModel->delete();
            }
            return;
        }

        if (empty($propertyModel)) {
            $propertyModel = new Properties;
            $propertyModel->id = $property['id'];
            $propertyModel->created_at = $now;
        } else {
            if ($propertyModel->updated_at == $now) {
                return;
            }
        }

        $propertyModel->updated_at = $now;
        $propertyModel->customs = json_encode($attributes['customs']);

        $type = array_values(array_diff(array_map('trim', $attributes['customs']['custom-62518']), ['']));

        if (isset($type[0])) {
            $propertyModel->type = $this->getProperty($type[0]);
        }

        unset($attributes['customs']);

        $propertyModel->attributes = json_encode($attributes);

        $relationships = [
            'contacts' => $property['relationships']['contacts'],
            'companies' => $property['relationships']['companies'],
        ];

        $propertyModel->relationships = json_encode($relationships);
        $propertyModel->save();
    }

    protected function getProperty($str)
    {
        switch($str) {
            case 'Аренда':
                return 4;
            case 'Продажа':
                return 3;
            default:
                return null;
        }
    }

    /**
     * @param $property
     */
    public function storeCompany($company)
    {
        $attributes = $company['attributes'];
        $relationships = $company['relationships'];

        $companyModel = Company::where('id', $company['id'])
            ->first();

        $status = null;

        if (isset($relationships['status']['data']) && !empty($relationships['status']['data'])) {
            $status = $relationships['status']['data']['id'];

            if ($status != Company::NOT_ACTIVE_STATUS) {//Не актиывный статус не записываем
                if (!empty($companyModel)) {
                    $companyModel->delete();
                }

                return;
            }
        }

        $now = Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s');

        if (!empty($attributes['discarded-at'])) {
            if (!empty($companyModel)) {
                $companyModel->delete();
            }
            return;
        }

        if (empty($companyModel)) {
            $companyModel = new Company();
            $companyModel->id = $company['id'];
            $companyModel->created_at = $now;
        } else {
            if ($companyModel->updated_at == $now) {
                return;
            }
        }

        $companyModel->updated_at = $now;
        $companyModel->customs = json_encode($attributes['customs']);
        $companyModel->status = $status;

        unset($attributes['customs']);

        $companyModel->attributes = json_encode($attributes);

        $relationshipsArray = [
            'contacts' => $relationships['contacts'],
            'companies' => $relationships['status'],
        ];

        $companyModel->relationships = json_encode($relationshipsArray);
        $companyModel->save();
    }
}
