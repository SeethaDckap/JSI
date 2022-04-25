<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Order;

use Epicor\Customerconnect\Model\ArPayment\Order\Address;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Epicor\Customerconnect\Model\ArPayment\Order\Address\Renderer as AddressRenderer;

/**
 * Ar payment Received Detail view   form
 *
 * @author  ECC 
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template =  'Epicor_Customerconnect::customerconnect/arpayments/order/info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        AddressRenderer $addressRenderer,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
       
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve current order model instance
     *
     * @return \Epicor\Customerconnect\Model\Arpayment\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }
}
