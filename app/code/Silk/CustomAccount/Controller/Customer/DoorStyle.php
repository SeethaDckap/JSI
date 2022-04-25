<?php

namespace Silk\CustomAccount\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Variable\Model\VariableFactory;
use \Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\App\ResourceConnection;

class DoorStyle extends \Magento\Framework\App\Action\Action
{
    protected $resourceConnection;

    protected $assetRepository;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        AssetRepository $assetRepository,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->assetRepository = $assetRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {

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

        return $this->resultJsonFactory->create()->setData($response);
    }
}
