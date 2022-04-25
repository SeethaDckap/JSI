<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Crqs\Listing\Renderer;


/**
 * Sales Rep CRQS Account short code Renderer
 * @category   Epicor
 * @package    Epicor_SalesRep
 */
class AccountShortcode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /*
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row returns Account number */
        $accountNumber = $row->getData($this->getColumn()->getIndex());
        return $this->checkERPAccountRegister($accountNumber);
    }

    public function checkERPAccountRegister($accountNumber)
    {

        $helper = $this->commHelper; /* @var $helper Epicor_Comm_Helper_Data */
        $company = $helper->getStoreBranding()->getCompany();
        $uomSep = $helper->getUOMSeparator();
        $registerName = $accountNumber . $company . $uomSep;

        if ($this->registry->registry($registerName) == '') { //check if short code is not in registry, get from collection and add to registry
            $shortCodeObj = $this->commResourceCustomerErpaccountCollectionFactory->create()
                ->addFieldToFilter('account_number', array('eq' => $accountNumber))
                ->addFieldToFilter('erp_code', array('like' => $company . $uomSep . '%'))
                ->addFieldToSelect('short_code');
            $shortCode = $shortCodeObj->getFirstItem()->getShortCode();
            if ($shortCode != '') { //if shortcode available, adds shortcode to registry
                $this->registry->register($registerName, $shortCode);
            } else { //if shortcode not available, adds row value to registry
                $this->registry->register($registerName, $accountNumber);
            }
        }
        
        return $this->registry->registry($registerName); // retrun registry
    }

}
