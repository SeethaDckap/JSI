<?php

namespace Silk\CustomAccount\Controller\Decoder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class Search extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    protected $varFactory;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        \Magento\Variable\Model\VariableFactory $varFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->varFactory = $varFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $searchString = $this->getRequest()->getParam('query');
        $doorStyleCode = $this->getRequest()->getParam('door_style');

        try {
            $tableName = $this->resourceConnection->getTableName('decoder_base_sku');
            $doorStyleTable = $this->resourceConnection->getTableName('decoder_doorstyle_info');
            $connection = $this->resourceConnection->getConnection();
            
            if($doorStyleCode){
                $doorStyleCodesQry = $connection
                    ->select()
                    ->from($doorStyleTable, ['included_code'])
                    ->where('code = ?', $doorStyleCode);

                $doorStyleCodeString = $connection->fetchCol($doorStyleCodesQry);
                if(!empty($doorStyleCodeString)){
                    $doorStyleCodes = explode(';', $doorStyleCodeString[0]);
                }
                else{
                    $doorStyleCodes = [$doorStyleCode];
                }
                
                $qry = $connection
                    ->select()
                    ->from($tableName, ['base_sku', 'assembled_price_refer', 'unassembled_price_refer'])
                    ->where('base_sku like "%' . $searchString . '%"')
                    ->where('door_style in (?)', $doorStyleCodes)
                    ->order('base_sku');
            }
            else{
                $qry = $connection
                    ->select()
                    ->from($tableName, ['base_sku', 'assembled_price_refer', 'unassembled_price_refer'])
                    ->where('base_sku like "%' . $searchString . '%"')
                    ->order('base_sku');
            }

            $result = $connection->fetchAll($qry);

            $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
            $productQry = $connection
                    ->select()
                    ->from($productTable, ['base_sku' => 'sku', 'assembled_price_refer' => 'sku', 'unassembled_price_refer' => 'sku'])
                    ->where('sku like "KX-' . $searchString . '%"')
                    ->order('sku');

            $productResult = $connection->fetchAll($productQry);

            $result = array_merge($result, $productResult);

            return $this->resultJsonFactory->create()->setData($result);

        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData([]);
        }
        
    }
}
