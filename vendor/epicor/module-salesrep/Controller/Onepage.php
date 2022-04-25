<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller;


//require_once('Mage' . '/' . 'Checkout' . '/' . 'controllers' . '/' . 'OnepageController.php');

/**
 * Shopping cart controller
 */
abstract class Onepage extends \Magento\Checkout\Controller\Onepage
{

    /**
     * @var \Magento\Framework\View\LayoutInterfaceFactory
     */
    protected $layoutInterfaceFactory;

    public function __construct(
        \Magento\Framework\View\LayoutInterfaceFactory $layoutInterfaceFactory
    ) {
        $this->layoutInterfaceFactory = $layoutInterfaceFactory;
    }
    protected function _getBillingHtml()
    {
        $layout = $this->layoutInterfaceFactory->create();
        /* @var $layout Mage_Core_Model_Layout */
        $update = $layout->getUpdate();
        $update->load('salesrep_checkout_onepage_billing');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        unset($update);
        unset($layout);
        return $output;
    }

    protected function _getShippingHtml()
    {
        $layout = $this->layoutInterfaceFactory->create();
        /* @var $layout Mage_Core_Model_Layout */
        $update = $layout->getUpdate();
        $update->resetHandles();
        $update->resetUpdates();
        $update->load('salesrep_checkout_onepage_shipping');
        $layout->generateXml();
        $layout->generateBlocks();

        $output = $layout->getOutput();
        unset($update);
        unset($layout);
        return $output;
    }

}
