<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Sales\Order;


class Returnlink extends \Magento\Sales\Block\Order\Info\Buttons
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct(
            $context,
            $registry,
            $httpContext,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Comm::epicor_comm/sales/order/returnlink.phtml');
    }

    public function canReturn()
    {

        $canReturn = false;
        $order = $this->getOrder();

        if ($order->getEccErpOrderNumber()) {
            $helper = $this->commonAccessHelper;
            /* @var $helper Epicor_Common_Helper_Access */

            $returnsHelper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */



            if ($returnsHelper->isReturnsEnabled() && $returnsHelper->checkConfigFlag('allow_create')) {
                $canReturn = $helper->customerHasAccess(
                    'Epicor_Comm', 'Returns', 'createReturnFromDocument', '', 'Access'
                );
            }
        }
        return $canReturn;
    }

    /**
     * Get url for return action
     *
     * @param Mage_Sales_Order $order
     * @return string
     */
    public function getReturnUrl()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $order = $this->getOrder();

        return $helper->getCreateReturnUrl('order', array(
                'order_number' => $order->getEccErpOrderNumber()
                )
        );
    }

}
