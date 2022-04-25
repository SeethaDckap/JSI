<?php

namespace Silk\CustomAccount\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Variable\Model\VariableFactory;
use Magento\Customer\Model\Session;

class Information extends \Magento\Framework\App\Action\Action
{
    protected $resourceConnection;

    protected $assetRepository;

    protected $resultJsonFactory;

    protected $varFactory;

    protected $customerSession;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        AssetRepository $assetRepository,
        VariableFactory $varFactory,
        Session $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->assetRepository = $assetRepository;
        $this->varFactory = $varFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }


    public function execute()
    {

        $response = [
            'doorstyle' => $this->getDoorStyle(),
            'warehouse_mapping' => $this->getWarehouseMapping(),
            'pickup_address' => $this->getPickupAddress()
        ];

        return $this->resultJsonFactory->create()->setData($response);
    }

    public function getPickupAddress(){
        $var = $this->varFactory->create()->loadByCode('pickup_location');
        $pickupAddress = '';
        if($var){
            $pickupAddress = json_decode($var->getPlainValue(), true);
        }
        
        if($pickupAddress){
            $customer = $this->customerSession->getCustomer();
            foreach ($pickupAddress as $warehouseName => &$address) {
                $address['firstname'] = $customer->getFirstname();
                $address['lastname'] = $customer->getLastname();
            }
        }

        return $pickupAddress;
    }

    public function getWarehouseMapping(){
        $tableName = $this->resourceConnection->getTableName('warehouse_mapping');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName);

        return $connection->fetchAll($qry);
    }

    public function getDoorStyle(){
        $tableName = $this->resourceConnection->getTableName('decoder_doorstyle_info');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName)
            ->where('show_in_list = 1')
            ->order('title');

        $result = $connection->fetchAll($qry);
        $series = ['Designer' => [], 'Premier' => [], 'Accent' => []];
        foreach ($result as $doorStyle) {
            $doorStyle['image'] = $this->assetRepository->getUrl("images/doorstyles/" . $doorStyle['title'] . ".jpg");
            $doorStyle['assemble'] = 0;
            $doorStyle['show_spec'] = 0;
            $doorStyle['is_open'] = 0;
            $doorStyle['assembled_price_refer'] = '';
            $doorStyle['unassembled_price_refer'] = '';
            $doorStyle['search_base_sku'] = '';
            $doorStyle['search_qty'] = '';
            $doorStyle['results'] = [];
            
            if($doorStyle['spec_image']){
                $doorStyle['spec_image'] = $this->assetRepository->getUrl("images/doorspec/" . $doorStyle['spec_image']);
            }
            
            if(!isset($series[$doorStyle['series']])){
                $series[$doorStyle['series']] = [];
            }
            
            $series[$doorStyle['series']][] = $doorStyle;
        }

        $response = [];
        foreach ($series as $title => $doorStyles) {
            $response[] = [
                'title' => $title,
                'items' => $doorStyles
            ];
        }

        return $response;
    }
}
