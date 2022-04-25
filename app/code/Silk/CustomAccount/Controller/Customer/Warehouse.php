<?php

namespace Silk\CustomAccount\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

class Warehouse extends \Magento\Framework\App\Action\Action
{
    protected $resourceConnection;

    protected $assetRepository;

    protected $resultJsonFactory;

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

        $tableName = $this->resourceConnection->getTableName('warehouse_mapping');
        $connection = $this->resourceConnection->getConnection();
        $qry = $connection
            ->select()
            ->from($tableName);

        $response = $connection->fetchAll($qry);

        return $this->resultJsonFactory->create()->setData($response);
    }
}
