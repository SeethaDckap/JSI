<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 57)
namespace Epicor\Customerconnect\Block\Widget;


class Button extends \Magento\Backend\Block\Widget\Button
{
    protected function _construct()
    {
        $this->setTemplate('Epicor_Common::widget/button.phtml');
    }
}
//M1 > M2 Translation End