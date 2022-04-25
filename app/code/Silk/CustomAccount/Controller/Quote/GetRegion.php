<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class GetRegion extends \Magento\Framework\App\Action\Action
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
        try {
            $country = $this->getRequest()->getParam('country') ? $this->getRequest()->getParam('country') : 'US';
            $tableName = $this->resourceConnection->getTableName('directory_country_region');
            $connection = $this->resourceConnection->getConnection();
            $qry = $connection
                ->select()
                ->from($tableName, ['region_id', 'code', 'default_name'])
                ->where('country_id = (?)', $country);

            $result = $connection->fetchAll($qry);

            $response = [
                'status' => 'success',
                'regions' => $result
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
}
