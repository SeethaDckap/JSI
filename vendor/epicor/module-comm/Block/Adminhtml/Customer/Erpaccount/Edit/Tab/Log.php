<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/**
 * Erp Account Log list
 *
 * @author David.Wylie
 */
class Log extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_erp_customer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = [])
    {
        $this->registry = $registry;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccount_logs');
        $this->setUseAjax(true);
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(false);
    }

    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            if ($this->registry->registry('customer_erp_account')) {
                $this->_erp_customer = $this->registry->registry('customer_erp_account');
            } else {
                $this->_erp_customer = $this->commCustomerErpaccountFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->_erp_customer;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Upload Logs';
    }

    public function getTabTitle()
    {
        return 'Upload Logs';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceMessageLogCollectionFactory->create();
        $erpCode = $this->getErpCustomer()->getErpCode();

        $collection->addFieldToFilter('message_parent', \Epicor\Comm\Model\Message::MESSAGE_TYPE_UPLOAD);
        $collection->addFieldToFilter('message_category', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_CUSTOMER);
        $collection->addFieldToFilter('message_subject', $erpCode);

        $helper = $this->commMessagingHelper;

        $messageTypes = $helper->getMessageTypes();

        if ($this->getErpCustomer()->isTypeSupplier()) {
            $filterMessageTypes = array();

            foreach ($messageTypes as $type => $desc) {
                if (strpos($desc, 'Supplier') !== false) {
                    $filterMessageTypes[] = strtoupper($type);
                }
            }
            $collection->addFieldToFilter('message_type', array('in' => $filterMessageTypes));
        } else {
            $filterMessageTypes = array();

            foreach ($messageTypes as $type => $desc) {
                if (strpos($desc, 'Supplier') === false) {
                    $filterMessageTypes[] = strtoupper($type);
                }
            }
            $collection->addFieldToFilter('message_type', array('in' => $filterMessageTypes));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_type', array(
            'header' => __('Message Type'),
            'align' => 'left',
            'index' => 'message_type',
            'renderer' => 'Epicor\Comm\Block\Renderer\Message',
        ));

        $this->addColumn('message_status', array(
            'header' => __('Message Status'),
            'align' => 'left',
            'index' => 'message_status',
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Messagestatus'
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
                            'source' => 'customer',
                            'sourceid' => $this->getErpCustomer()->getId())
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Reprocess'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_log/reprocess',
                        'params' => array(
                            'source' => 'customer',
                            'sourceid' => $this->getErpCustomer()->getId()
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
            'source' => 'customer',
            'sourceid' => $this->getErpCustomer()->getId(),
            'id' => $row->getId()
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/view', $params);
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/logsgrid', $params);
    }

}
