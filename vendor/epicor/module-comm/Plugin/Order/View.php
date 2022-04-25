<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Plugin
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order;

/**
 * Class View
 */
class View
{


    /**
     * Before Get Email Url.
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject Subject.
     * @param string                                    $result  Url string.
     *
     * @return string
     */
    public function afterGetEmailUrl(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        return $subject->getUrl('sales/*/email', ['force_send' => 1]);

    }//end afterGetEmailUrl()


}//end class
