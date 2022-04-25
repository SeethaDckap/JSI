<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View\Tab;


    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * Description of Log
 *
 * @author David.Wylie
 */
class Log extends \Magento\Framework\View\Element\Text\ListText  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory
     */
    protected $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\LogurlFactory
     */
    protected $commAdminhtmlWidgetGridColumnRendererLogurlFactory;

    /*public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\LogurlFactory $commAdminhtmlWidgetGridColumnRendererLogurlFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = [])
    {
        $this->registry = $registry;
        $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory = $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;
        $this->commAdminhtmlWidgetGridColumnRendererLogurlFactory = $commAdminhtmlWidgetGridColumnRendererLogurlFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;

        $this->setId('order_message_log');
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);

        parent::__construct($context, $backendHelper, $data);
    }*/


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

    /**
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->registry->registry('current_order')) {
            $this->registry->register('current_order', $this->salesOrderFactory->create()->load($this->getRequest()->getParam('order_id')));
        }
        return $this->registry->registry('current_order');
    }

    protected function _prepareCollection()
    {
        $orderId = $this->getOrder()->getQuoteId();

        $collection = $this->commResourceMessageLogCollectionFactory->create();


        /* @var $collection \Epicor\Comm\Model\ResourceModel\Erp\Customer\Sku\Collection */
        $collection->addFieldToFilter('message_category', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_ORDER);
        $collection->addFieldToFilter('message_secondary_subject', array('like' => '%Basket Quote ID: ' . $orderId . '%'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_type', array(
            'header' => __('Message Type'),
            'align' => 'left',
            'index' => 'message_type'
        ));

        $this->addColumn('message_status', array(
            'header' => __('Message Status'),
            'align' => 'left',
            'index' => 'message_status',
            'renderer' => $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory->create()
        ));
        $this->addColumn('message_subject', array(
            'header' => __('Subject'),
            'align' => 'left',
            'index' => 'message_subject'
        ));

        $this->addColumn('start_datestamp', array(
            'header' => __('Start Time'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'start_datestamp',
        ));

        $this->addColumn('duration', array(
            'header' => __('Duration (ms)'),
            'align' => 'left',
            'index' => 'duration'
        ));

        $this->addColumn('status_code', array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status_code'
        ));

        $this->addColumn('status_description', array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'status_description'
        ));

        $this->addColumn('url', array(
            'header' => __('Url'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'url',
            'renderer' => $this->commAdminhtmlWidgetGridColumnRendererLogurlFactory->create(),
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_log/view',
                        'params' => array(
                            'source' => 'order',
                            'sourceid' => $this->getOrder()->getId()
                        )
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array(
            'source' => 'order',
            'sourceid' => $this->getOrder()->getId(),
            'id' => $row->getId()
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/view', $params);
    }

    /*public function getGridUrl()
    {
        return $this->getUrl('adminhtml/epicorcomm_sales_order/loggrid', array('_current' => true));
    }

    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/epicorcomm_sales_order/loggrid', array('_current' => true));
    }*/


}
