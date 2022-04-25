<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ContactCode extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * DefaultValue constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerCustomerFactory = $customerCustomerFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $customerId = $this->context->getRequestParam('id');
        if ($customerId) {
            $customerModel = $this->customerCustomerFactory->create()->load($customerId);
            if($customerModel->getEccErpAccountType() == 'customer') {
                $this->_data['config']['visible'] = false;
            }

        }
    }

}
