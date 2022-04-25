<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Orderstatus\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Type
 *
 * @author Paul.Ketelle
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Sales\Model\Order\Status
     */
    protected $salesOrderStatus;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Model\Order\Status $salesOrderStatus,
        array $data = []
    ) {
        $this->salesOrderStatus = $salesOrderStatus;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $status = $this->salesOrderStatus->load($row['status'], 'status');
        if ($status['label']) {

            return $status['label'];
        } else {
            return 'No Matching Order Status';
        }
    }

}
