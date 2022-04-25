<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Cart;

class AdditionalConfig extends \Magento\Checkout\Block\Cart\AbstractCart
{


    /**
     * Aafter GetConfig Plugin.
     *
     * @param Magento\Checkout\Block\Cart\Sidebar $subject
     * @param  array $return
     * @return mixed
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, $return)
    {
        $return['isPunchout'] = $this->_customerSession->getIsPunchout();
        $return['punchoutUrl'] =  $this->getUrl('punchout/punchout');
        return $return;
    }

}
