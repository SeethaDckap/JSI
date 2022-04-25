<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Observer;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $registry;

    protected $commEntityregHelper;

    protected $eventManager;

    /**
     * @var \Epicor\Supplierconnect\Model\ModelReader
     */
    protected $modelReader;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Supplierconnect\Model\ModelReader $modelReader,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->registry = $registry;
        $this->commEntityregHelper = $commEntityregHelper;
        $this->eventManager = $eventManager;
        $this->modelReader = $modelReader;
        $this->customerSession = $customerSession;
    }


    protected function updateEntityRegistration($entity, $type)
    {
        $helper = $this->commEntityregHelper;
        $helper->updateEntityRegistration($entity->getId(), $type);
    }

    protected function removeEntityRegistration($entity, $type)
    {
        $helper = $this->commEntityregHelper;
        $helper->removeEntityRegistration($entity->getId(), $type);
    }



    protected function purgeItem($item, $model, $type)
    {
        $entity = $this->modelReader->getModel($model)->load($item->getEntityId());
        if (!$entity->isObjectNew()) {
            $params = array(
                'entity' => $entity,
                'register' => $item
            );
            $this->eventManager->dispatch('epicor_comm_entity_purge_' . $type . '_before', $params);
            $entity->delete();
            $this->eventManager->dispatch('epicor_comm_entity_purge_' . $type . '_before', $params);
        }
    }

}