<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer;


/**
 * Customer Account Type Grid Renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Accounttype extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        array $data = []
    ) {
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commonAccountSelectorHelper;
        /* @var $helper Epicor_Common_Helper_Account_Selector */
        $accountType = $row->getEccErpaccountId() ? lcfirst($row->getErpAccountType()) : $row->getEccErpAccountType();
        $customAccountType = $row->getLinkedErpAccountType(); // Dealer & Distributor custom added
        if($customAccountType =='Dealer' || $customAccountType =='Distributor'){
                return $customAccountType;
        }
        
        $accountTypes = $helper->getAccountTypes();
        $accountTypeLabel = isset($accountTypes[$accountType]) && isset($accountTypes[$accountType]['label']) ? $accountTypes[$accountType]['label'] : '';
        
        return $accountTypeLabel;
    }

}
