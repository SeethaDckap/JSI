<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Balances\Aged\Listing;


/**
 * Customer Period balances list Grid config
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Account\Balances\Grid
{


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $customerconnectHelper,
            $registry,
            $dataObjectFactory,
            $data
        );

        $this->setId('customer_account_agedbalances_list');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('customerconnect');

        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        //      $this->setRowUrlValue('*/*/editContact');

        $this->setMessageType('cuad');
        $this->setDataSubset('contacts/contact');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $details = $this->registry->registry('customer_connect_account_details');

        if ($details) {
            $balanceInfo = $this->processBalances($details->getVarienDataArrayFromPath('account/aged_balances/aged_balance'));

            $this->setCustomColumns($balanceInfo['columns']);
            $this->setCustomData($balanceInfo['balances']);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
        }
    }

}
