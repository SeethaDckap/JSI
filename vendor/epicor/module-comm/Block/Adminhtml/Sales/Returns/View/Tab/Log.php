<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab;

/**
 * Erp Account Log list
 *
 * @author David.Wylie
 */
class Log extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $_erp_customer;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory, \Magento\Framework\Registry $registry, \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory, \Epicor\Comm\Block\Renderer\MessageFactory $commRendererMessageFactory, \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory, $attributes = array()) {
        $this->commRendererMessageFactory = $commRendererMessageFactory;
        $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory = $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        $this->registry = $registry;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        parent::__construct(
                $context, $backendHelper, $attributes);
        $this->setId('return_logs');
        $this->setUseAjax(true);
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    public function canShowTab() {
        return true;
    }

    public function getTabLabel() {
        return 'Message Logs';
    }

    public function getTabTitle() {
        return 'Message Logs';
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceMessageLogCollectionFactory->create();

        $return = $this->getReturn();
        /* @var $return Epicor_Comm_Model_customer_Return */

        $erpReturn = $return->getErpReturnsNumber() ?: 'XXXX';

        $webReturn = $return->getWebReturnsNumber() ?: $return->getId();
        $collection->addFieldToFilter(
                'message_secondary_subject', array(
            array('like' => '%ERP Return: ' . $erpReturn . '%'),
            array('like' => '%Web Return: ' . $webReturn . '%')
                )
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getReturn() {
        if (!$this->registry->registry('return')) {
            $this->registry->register('return', $this->commCustomerReturnModelFactory->create()->load($this->getRequest()->getParam('id')));
        }
        return $this->registry->registry('return');
    }

    protected function _prepareColumns() {
        $this->addColumn('message_type', array(
            'header' => __('Message Type'),
            'align' => 'left',
            'index' => 'message_type',
            'renderer' => '\Epicor\Comm\Block\Renderer\Message',
        ));

        $this->addColumn('message_status', array(
            'header' => __('Message Status'),
            'align' => 'left',
            'index' => 'message_status',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Messagestatus',
        ));

        $this->addColumn('message_secondary_subject', array(
            'header' => __('Secondary Subject'),
            'align' => 'left',
            'index' => 'message_secondary_subject'
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
            'index' => 'duration',
            'type' => 'number'
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
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array(
                        'base' => 'adminhtml/epicorcomm_message_log/view',
                        'params' => array(
                            'source' => 'return',
                            'sourceid' => $this->getReturn()->getId())
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Reprocess'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_log/reprocess',
                        'params' => array(
                            'source' => 'return',
                            'sourceid' => $this->getReturn()->getId()
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

    public function getRowUrl($row) {
        $params = array(
            'source' => 'return',
            'sourceid' => $this->getReturn()->getId(),
            'id' => $row->getId()
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/view', $params);
    }

    public function getGridUrl() {
        $params = array(
            'id' => $this->getReturn()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_returns/logsgrid', $params);
    }

}
