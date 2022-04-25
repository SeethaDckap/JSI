<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Epicor\Comm\Helper\BsvAndGor;

class MassAssignErpstatus extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var Registry
     */
    protected $registry;
    
     /**
     * @var \Epicor\Comm\Helper\BsvAndGor
     */
    protected $bsvAndGorHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BsvAndGor $bsvAndGorHelper
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->bsvAndGorHelper = $bsvAndGorHelper;
    }


    protected function massAction(AbstractCollection $collection)
    {
        $status = $this->getRequest()->getParam('erp_status');
        foreach ($collection as $order) {
            $this->changeErpstatus($order, $status);
        }
        $this->messageManager->addSuccessMessage(__(count($collection) . ' Order Erp Status changed'));
        $this->_redirect('sales/order/index');
    }


    protected function changeErpstatus($order, $status)
    {
        $gor_message = 'Order Not Sent';
        $state = '';
        switch ($status) {
            case 0:
                $gor_message = 'Manually set to : Order Not Sent';
                $state = 'processing';
                break;
            case 1:
                $gor_message = 'Manually set to : Order Sent';
                break;
            case 3:
                $gor_message = 'Manually set to : Erp Error';
                break;
            case 4:
                $gor_message = 'Manually set to : Error - Retry Attempt Failure';
                break;
            case 5:
                $gor_message = 'Manually set to : Order Never Send';
                break;
        }

        if ($order->getEccGorSent() != $status) {
            $this->registry->register("offline_order_{$order->getId()}", true);
            if($order->getEccGorSent() == 4 && $status == 0){ //Reset Retry Count
                $order->setEccGorSentCount(0); 
            }
            $order->setEccGorSent($status);
            $order->setEccGorMessage($gor_message);
            if (!empty($state)) {
                $order->setState($state);
            }
            //save
            $order->save();
            
            //Send BSV and GOR
            $this->bsvAndGorHelper->SendOrderToErp($order);
        }
    }

}
