<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Quotes\Plugin\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;

class Toolbar
{
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof  \Epicor\Quotes\Block\Adminhtml\Quotes\Edit) {
            return [$context, $buttonList];
        }

            $buttonList->remove('delete');
            $buttonList->remove('save');
            $buttonList->remove('reset');
        return [$context, $buttonList];
    }
}