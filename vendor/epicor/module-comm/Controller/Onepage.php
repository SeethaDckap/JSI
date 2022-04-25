<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;

//require_once('Mage' . '/' . 'Checkout' . '/' . 'controllers' . '/' . 'OnepageController.php');

/**
 * Shopping cart controller
 */
abstract class Onepage extends \Magento\Checkout\Controller\Onepage
{
protected function _getShippingDatesHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shipping_dates');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }
}
