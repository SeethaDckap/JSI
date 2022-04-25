<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts;

class Info extends \Epicor\Customerconnect\Block\Customer\Info {

    protected $_infoData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory
     */
    protected $customerconnectListingRendererLinkorderFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $registry, \Epicor\Lists\Helper\Data $listsHelper, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory $customerconnectListingRendererLinkorderFactory, \Magento\Framework\DataObjectFactory $dataObjectFactory, array $data = []
    ) {
        $this->customerconnectListingRendererLinkorderFactory = $customerconnectListingRendererLinkorderFactory;
        $this->registry = $registry;
        $this->listsHelper = $listsHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct(
                $context, $customerconnectHelper, $data
        );
    }

    public function _construct() {
        parent::_construct();
//        $this->setTemplate('epicor/lists/contract/info.phtml');
//        $this->setColumnCount(3);

        $details = $this->registry->registry('epicor_lists_contracts_details');
        if ($details) {

            $contract = $details->getContract();

            if (is_object($contract->getSalesRep())) {
                $salesRepName = (trim($contract->getSalesRep()->getName()) != '') ? $contract->getSalesRep()->getName() : $contract->getSalesRep()->getNumber();
            } else {
                $salesRepName = $contract->getSalesRep();
            }
            $renderer = $this->customerconnectListingRendererLinkorderFactory->create();
            $columnData = $this->dataObjectFactory->create();
            $columnData->setIndex('our_order_number');
            $renderer->setColumn($columnData);
            $this->_infoData = array(
                __('Contract Code :')->render() => $contract->getContractCode(),
                __('Title :')->render() => $contract->getContractTitle(),
                __('Start Date :')->render() => $contract->getStartDate(),
                __('End Date :')->render() => date('jS M Y', strtotime($contract->getEndDate())),
                __('Status :')->render() => $contract->getContractStatus() == 'A' ? 'Active' : 'Inactive',
                __('Last Modified Date :')->render() => date('jS M Y', strtotime($contract->getLastModifiedDate())),
                __('Sales Rep :')->render() => $salesRepName,
                __('Contact Name :')->render() => $contract->getContactName(),
                __('PO Number :')->render() => $contract->getPurchaseOrderNumber()
            );

            $this->setTitle(__('Customer Contract Information'));
//        $this->setColumnCount(1);
//        $this->setOnRight(true);
        }
    }

}
