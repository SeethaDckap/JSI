<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Order\Info;

use Epicor\OrderApproval\Block\Order\View\ApprovalActions;

/**
 * Orders info buttons block override
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var string
     */
    protected $_template = 'Epicor_Common::epicor_common/sales/order/info/buttons.phtml';

    /**
     * @var ApprovalActions
     */
    private $approvalActions;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        ApprovalActions $approvalActions,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $httpContext,
            $data
        );
        $this->approvalActions = $approvalActions;
    }


    public function getReorderUrl($order)
    {
        return $this->getUrl('epicor/sales_order/reorder', array('order_id' => $order->getId()));
    }

    /**
     * @return bool
     */
    public function isVisibleForOrderApprovals()
    {
        if ($this->approvalActions->isApprovalSectionVisible()) {
            return false;
        }
        return true;
    }

}
