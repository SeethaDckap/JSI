<?php

namespace Silk\CustomAccount\Controller\Decoder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class Import extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $resourceConnection;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }


    public function execute()
    {   
        try {
            $files = $this->getRequest()->getFiles()->toArray();
            $result = ['products' => []];
            if(!empty($files) && isset($files['import_file']) && !empty($files['import_file'])){
                $file = $files['import_file']['tmp_name'];
                $handle = fopen($file, 'r');
                while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                    if(ctype_digit($data[0])){
                        $sku = $data[2];
                        $baseSku = $this->searchBaseSKU($sku);
                        if(!$baseSku){
                            $baseSku = $sku;
                        }
                        $data = [
                            'sku' => $sku,
                            'qty' => $data[1],
                            'base_sku' => $baseSku,
                            'code' => strtok($sku, '-')
                        ];

                        $result['products'][] = $data;
                    }
                        
                }

            }

            return $this->resultJsonFactory->create()->setData($result);

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            return $this->resultJsonFactory->create()->setData($result);
        }
    }

    private function searchBaseSKU($sku){
        $tableName = $this->resourceConnection->getTableName('decoder_base_sku');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName, ['base_sku'])
            ->where("? like CONCAT('%', base_sku, '%')", $sku)
            ->order('LENGTH(base_sku) desc')
            ->limit(1);

        $result = $connection->fetchCol($qry);

        return !empty($result) ? $result[0] : null;
    }
}
