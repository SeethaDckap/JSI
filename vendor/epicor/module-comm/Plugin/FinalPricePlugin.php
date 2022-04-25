<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin;

class FinalPricePlugin {

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    public function __construct(
    \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory) {
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
    }

    public function afterHasSpecialPrice(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $result) {
        
        $msq = $this->commMessageRequestMsqFactory->create();
        if ($msq->isActive() && $subject->getSaleableItem()->getTypeId() == 'grouped') {
            $displayRegularPrice = $subject->getPriceType('regular_price')->getAmount()->getValue();
            $displayFinalPrice = $subject->getPriceType('special_price')->getAmount()->getValue();
            return ($displayFinalPrice && ($displayFinalPrice < $displayRegularPrice));
        }
        return $result;
    }

}
