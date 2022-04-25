<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Notes block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Notes extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{



    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $commReturnsHelper, $registry, $data);
       
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Notes'));
        $this->setTemplate('epicor_comm/customer/returns/notes.phtml');
    }

    public function getNoteText()
    {
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        $note = ($return) ? $return->getNoteText() : '';

        return $this->escapeHtml($note);
    }


    public function noteTabLengthLimit()
    {
        return $this->scopeConfig->getValue('epicor_comm_returns/notes/tab_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function noteTabRequired()
    {
        return $this->scopeConfig->getValue('epicor_comm_returns/notes/tab_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
