<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class Approve extends WidgetAbstractRenderer
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * Approve constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $rowDate = $row->getData('created_at');
        $time = $this->dateTimeFactory->create();

        return $time->date('m/d/y', strtotime($rowDate));
    }
}