<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Epicor\OrderApproval\Model\Approval\HistoryStatus;

class Status extends AbstractRenderer
{
    /**
     * @var HistoryStatus
     */
    private $historyStatus;

    /**
     * Status constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param HistoryStatus $historyStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        HistoryStatus $historyStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->historyStatus = $historyStatus;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getData('approved_status');
    }
}