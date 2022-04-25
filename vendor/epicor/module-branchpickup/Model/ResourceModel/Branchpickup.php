<?php
namespace Epicor\BranchPickup\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class Branchpickup
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Branchpickup constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * get multi shipping value from quote
     *
     * @param $quoteId
     * @return string
     */
    public function isMultiShipping($quoteId)
    {
        $tableName = $this->resourceConnection->getTableName('quote');
        $column = 'is_multi_shipping';
        if ($quoteId) {
            $connection = $this->resourceConnection->getConnection();
            $sqlQuery = $connection->select()
                ->from($tableName, $column)
                ->where('entity_id = ?', $quoteId);
            return $connection->fetchOne($sqlQuery);
        }
    }
}
