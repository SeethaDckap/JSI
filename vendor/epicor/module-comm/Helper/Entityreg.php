<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


/**
 * Entity Registry Helper
 *
 * @category    Epicor
 * @package     Epicor_Comm
 * @author      Epicor Websales Team
 */
class Entityreg extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory
     */
    protected $commResourceEntityRegisterCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Entity\RegisterFactory
     */
    protected $commEntityRegisterFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory $commResourceEntityRegisterCollectionFactory,
        \Epicor\Comm\Model\Entity\RegisterFactory $commEntityRegisterFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->commResourceEntityRegisterCollectionFactory = $commResourceEntityRegisterCollectionFactory;
        $this->commEntityRegisterFactory = $commEntityRegisterFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
    /**
     * Gets a entity_registry row for the provided entity details
     * 
     * @param integer $entityId
     * @param string $type
     * @param integer $childId
     * 
     * @return \Epicor\Comm\Model\Entity\Register
     */
    public function getEntityRegistration($entityId, $type, $childId = null)
    {
        $collection = $this->commResourceEntityRegisterCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Entity\Register\Collection */
        $collection->addFieldToFilter('entity_id', $entityId);
        $collection->addFieldToFilter('type', $type);

        if (!is_null($childId)) {
            $collection->addFieldToFilter('child_id', $childId);
        }

        $entityData = $collection->getFirstItem();
        $entity = $this->commEntityRegisterFactory->create();

        if ($entityData->getId()) {
            $entity = $entity->load($entityData->getId());
        }

        return $entity;
    }

    /**
     * Updates an entity_registry row for the provided entity details
     * 
     * @param integer $entityId
     * @param type $type
     * @param integer $childId
     */
    public function updateEntityRegistration($entityId, $type, $childId = null)
    {
        $entityReg = $this->getEntityRegistration($entityId, $type, $childId);
        /* @var $entityReg \Epicor\Comm\Model\Entity\Register */
        if ($entityReg->isObjectNew()) {
            $entityReg->setEntityId($entityId);
            $entityReg->setChildId($childId);
            //M1 > M2 Translation Begin (Rule 25)
            //$entityReg->setCreatedAt(now());
            $entityReg->setCreatedAt(date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
            $entityReg->setType($type);
        }

        $entityReg->setIsDirty(0);
        //M1 > M2 Translation Begin (Rule 25)
        //$entityReg->setModifiedAt(now());
        $entityReg->setModifiedAt(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End

        $entityReg->save();
    }

    /**
     * Makes all entity_registry rows dirty for the given types
     * 
     * @param array $types
     */
    public function dirtyEntityRegistrations($types)
    {

        $entityReg = $this->commEntityRegisterFactory->create();
        /* @var $entityReg \Epicor\Comm\Model\Entity\Register */
        if (!empty($types)) {
            $typeString = '';
            $join = '';
            foreach ($types as $type) {
                $typeString .= $join . '\'' . $type . '\'';
                $join = ',';
            }

            $entityTable = $entityReg->getResource()->getTable('ecc_entity_register');

            $query = 'UPDATE ' . $entityTable . ' SET is_dirty = 1 WHERE type IN (' . $typeString . ')';
            $this->_runQuery($query);
        }
    }

    /**
     * Removes an entity registry rows matching the given details
     * 
     * @param integer $entityId
     * @param type $type
     * @param integer $childId
     */
    public function removeEntityRegistration($entityId, $type, $childId = null)
    {
        $entityReg = $this->getEntityRegistration($entityId, $type, $childId);
        /* @var $entityReg \Epicor\Comm\Model\Entity\Register */

        if (!$entityReg->isObjectNew()) {
            $entityReg->delete();
        }
    }

    /**
     * Gets the registry types for the given message types
     * 
     * @param array $types
     */
    public function getRegistryTypes($types)
    {
        $registryTypes = array();

        $uploadMessages = $this->getMessageTypes('upload');
        foreach ($types as $type) {
            $type = strtolower($type);
            if (isset($uploadMessages[$type])) {
                $message = (array) $uploadMessages[$type];

                if (isset($message['registry_type'])) {
                    if (strpos($message['registry_type'], ',') !== false) {
                        $registryTypes = array_merge($registryTypes, explode(',', $message['registry_type']));
                    } else {
                        $registryTypes[] = $message['registry_type'];
                    }
                }
            }
        }
        return array_unique($registryTypes);
    }

    /**
     * Gets the registry types for the given message types
     * 
     * @param array $types
     */
    public function getRegistryTypeDescriptions($types = array())
    {
        $registryTypes = array();

        $returnAll = empty($types) ? true : false;

        $uploadMessages = $this->getMessageTypes('upload');

        foreach ($uploadMessages as $ltype => $message) {

            $utype = strtoupper($ltype);
            if ($returnAll || in_array($utype, $types) || in_array($ltype, $types)) {
                $message = (array) $message;
                if (isset($message['registry_type_desc'])) {
                    $registryTypes[$utype] = $message['registry_type_desc'];
                }
            }
        }

        return array_unique($registryTypes);
    }

    private function _runQuery($query)
    {
        /**
         * Get the resource model
         */
        $resource = $this->resourceConnection;

        /**
         * Retrieve the write connection
         */
        $writeConnection = $resource->getConnection('core_write');

        /**
         * Execute the query
         */
        $writeConnection->query($query);
    }

}
