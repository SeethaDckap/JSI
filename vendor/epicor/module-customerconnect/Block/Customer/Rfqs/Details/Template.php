<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * Cart item comment
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Template extends \Epicor\AccessRight\Block\Template
{

    const FRONTEND_RESOURCE_CREATE = 'Epicor_Customerconnect::customerconnect_account_rfqs_create';

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_rfqs_edit';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;
    /**  
     * @var \Magento\Framework\Registry  
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->registry = $registry;
        $this->formKey = $formKey;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/checkout/cart/item/comment.phtml');
    }

    public function getRegistry($value)
    {
        return $this->registry->registry($value);
    }
    
     /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }
}
