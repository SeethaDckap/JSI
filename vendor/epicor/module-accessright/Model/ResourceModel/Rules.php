<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;

/**
 * Admin rule resource model
 */
class Rules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Root ACL resource
     *
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResource;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    private $aclDataCache;

    /**
     * Rules constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Epicor\AccessRight\Acl\RootResource $rootResource
     * @param null $connectionName
     * @param \Magento\Framework\Acl\Data\CacheInterface|null $aclDataCache
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Epicor\AccessRight\Acl\RootResource $rootResource,
        $connectionName = null,
        \Magento\Framework\Acl\Data\CacheInterface $aclDataCache = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_rootResource = $rootResource;
        $this->_logger = $logger;
        $this->aclDataCache = $aclDataCache ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Config\CacheInterface::class
        );
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_access_role_rule', 'rule_id');
    }

    /**
     * Save ACL resources
     *
     * @param \Magento\Authorization\Model\Rules $rule
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRel(\Epicor\AccessRight\Model\Rules $rule)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $roleId = $rule->getAccessRoleId();

            $condition = ['access_role_id = ?' => (int)$roleId];

            $connection->delete($this->getMainTable(), $condition);

            $postedResources = $rule->getResources();
            if ($postedResources) {
                $row = [
                    'resource_id' => $this->_rootResource->getId(),
                    'privileges' => '', // not used yet
                    'access_role_id' => $roleId,
                    'permission' => 'allow',
                ];

                // If all was selected save it only and nothing else.
                if ($postedResources === [$this->_rootResource->getId()]) {
                    $insertData = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject($row),
                        $this->getMainTable()
                    );

                    $connection->insert($this->getMainTable(), $insertData);
                } else {
                    /** Give basic admin permissions to any admin */
                    $postedResources[] = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
                    $postedResources = array_unique($postedResources);
                    foreach ($postedResources as $resourceId) {
                        $row['resource_id'] = $resourceId;
                        $row['permission'] = 'allow' ;

                        $insertData = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject($row),
                            $this->getMainTable()
                        );
                        $connection->insert($this->getMainTable(), $insertData);
                    }
                }
            }

            $connection->commit();
            $this->aclDataCache->clean();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }
}
