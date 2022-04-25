<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml;

use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class CustomerOrdersTab
 *
 * @package Magento\Sales\Block\Adminhtml
 */
class CustomersTab extends TabWrapper
{
    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Return Tab label
     *
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Customers');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('orderapproval/*/customers', ['_current' => true]);
    }
}
