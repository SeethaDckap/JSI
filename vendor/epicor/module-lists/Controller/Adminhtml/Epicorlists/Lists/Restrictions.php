<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Restrictions extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\Restriction\CollectionFactory
     */
    protected $listsResourceListModelAddressRestrictionCollectionFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\Restriction\CollectionFactory $listsResourceListModelAddressRestrictionCollectionFactory
    ) {
        $this->listsResourceListModelAddressRestrictionCollectionFactory = $listsResourceListModelAddressRestrictionCollectionFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $list = $this->loadEntity();
        if ($list->getId()) {
            $restrictedPurchase = $this->listsResourceListModelAddressRestrictionCollectionFactory->create()
                ->addFieldToFilter('list_id', $list->getId())
                ->addFieldToSelect('restriction_type');
            $restArray =[];
            foreach ($restrictedPurchase->getData() as $key => $value) {
                $restArray[$key] = $value['restriction_type'];
            }
            $listRestrictions = array_values(array_unique($restArray));
            $this->backendAuthSession->setRestrictionTypeValue($listRestrictions[0] ?? '');
        }

        $rtValue = $this->backendAuthSession->getRestrictionTypeValue();
        if (empty($rtValue)) {
            $this->backendAuthSession->setRestrictionTypeValue(\Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ADDRESS);
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('restrictions_grid');
        $this->_view->renderLayout();
    }

}
