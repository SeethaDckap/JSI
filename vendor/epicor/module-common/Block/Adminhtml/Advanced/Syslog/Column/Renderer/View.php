<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Syslog grid item renderer
 *
 */

namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog\Column\Renderer;

use Epicor\Common\Model\LogView;

class View extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    private $logView;

    /**
     * View constructor.
     * @param LogView $logView
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        LogView $logView,
        \Magento\Backend\Block\Context $context, array $data = []
    ){
        parent::__construct($context, $data);
        $this->logView = $logView;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        return $this->logView->getViewDownLoad($row->getData('name'), 'system');
    }
}
