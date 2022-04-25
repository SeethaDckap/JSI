<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ Line quick add block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Lineadd extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/line_add.phtml');
        $this->setTitle(__('Add Line'));
    }

    /**
     * Checks to see if the autocomplete is allowed
     */
    public function autocompleteAllowed()
    {
        return $this->scopeConfig->isSetFlag('quickadd/autocomplete_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks to see if the autocomplete is allowed
     */
    public function customAllowed()
    {
        return $this->scopeConfig->isSetFlag('customerconnect_enabled_messages/crq_options/quickadd_custom_allowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
