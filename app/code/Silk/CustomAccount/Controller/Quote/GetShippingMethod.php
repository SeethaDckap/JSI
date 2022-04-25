<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class GetShippingMethod extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;

    protected $resourceConnection;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/warehouse.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

        try {
            $zipCode = $this->getRequest()->getParam('zipcode');
            $state = $this->getRequest()->getParam('state');
            $logger->info("Zipcode: " . $zipCode);
            $logger->info("State: " . $state);
            $defaultWarehouse = 'Fall River';
            $fulfillWarehouse = $this->getWarehouseByState($state, $zipCode);
            $shipments = [
                [
                    "name" => "Customer Ship",
                    "shipping_carrier_code" => "freeshipping",
                    "shipping_method_code" => "freeshipping",
                    "price" => 0,
                    "type" => 'pickup'
                ],
                [
                    "name" => "Customer Pickup",
                    "shipping_carrier_code" => "freeshipping",
                    "shipping_method_code" => "freeshipping",
                    "price" => 0,
                    "type" => 'pickup'
                ]
            ];

            if($defaultWarehouse == $fulfillWarehouse){
                $shipments[] = [
                    "name" => "Real time LTL Shipping Placehold",
                    "shipping_carrier_code" => "flatrate",
                    "shipping_method_code" => "flatrate",
                    "price" => 0,
                    'type' => 'ship'
                ];
            }

            $response = [
                'status' => 'success',
                'methods' => $shipments,
                'warehouse' => $fulfillWarehouse
            ];

        } catch (\Exception $e) {
            // $response = [
            //     'status' => 'error',
            //     'error' => $e->getMessage()
            // ];
            var_dump($e->getMessage());
        }
        

        return $this->resultJsonFactory->create()->setData($response);
    }

    public function getWarehouseByState($state, $zipcode){
        $tableName = $this->resourceConnection->getTableName('warehouse_mapping');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['warehouse'])
            ->where('state = (?)', $state)
            ->where(new \Zend_Db_Expr("zipcode = " . $zipcode . " or zipcode = ''"));

        $result = $connection->fetchCol($qry);

        return $result ? $result[0] : '';
    }
}
