<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Balances;


/**
 * Customer Period balances list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    
    protected $arpaymentsHelper;    

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->arpaymentsHelper = $arpaymentsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
    }
    /**
     * Processes balances into an array of balance column data for rendering
     * 
     * @param type $balanceData
     * 
     * @return array
     */
    protected function processBalances($balanceData)
    {
        $balances = array(
            0 => $this->dataObjectFactory->create()
        );
        $columns = array();
        $helper = $this->customerconnectHelper;
        $arpaymentsHelper = $this->arpaymentsHelper;
        $currencySymbol =$helper->getCurrencyCode();        

        foreach ($balanceData as $balance) {
            $number = $balance->getData('_attributes')->getNumber();
            $balances[0]->setData($number, $balance->getBalance());
            $sortName = 'aged_balance_filter_'.$number;
            $columns[$sortName] = array(
                'header' => __($balance->getDescription()),
                'align' => 'left',
                'type' => 'currency',
                'filter' => false,
                'sortable' => false,
                'currency_code' => $this->customerconnectHelper->getCurrencyMapping($currencySymbol, \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO),
                'index' => $number,
                'header_css_class' =>'aged_bal',
            );
        }

        ksort($columns);

        return array(
            'balances' => $balances,
            'columns' => $columns,
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

}