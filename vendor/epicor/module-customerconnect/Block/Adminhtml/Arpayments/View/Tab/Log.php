<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Arpayments\View\Tab;

class Log extends \Magento\Framework\View\Element\Text\ListText  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Messaging Log';
    }

    public function getTabTitle()
    {
        return 'Messaging Log';
    }

    public function isHidden()
    {
        return false;
    }
}
