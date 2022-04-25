<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs;


/**
 * Supplier Connect RFQ Block Template
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Template extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Supplier::supplier_rfqs_edit';

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

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

}
