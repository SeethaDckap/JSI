<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Message Log Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Messagelog extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $list;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Block\Renderer\MessageFactory
     */
    protected $commRendererMessageFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory
     */
    protected $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Epicor\Comm\Block\Renderer\MessageFactory $commRendererMessageFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory,
        $attributes = array(),
        array $data = []
        )
    {
        $this->commRendererMessageFactory = $commRendererMessageFactory;
        $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory = $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        ,$attributes);
        $this->setId('messagelog_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!$this->list) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Is this tab shown?
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab Label
     *
     * @return boolean
     */
    public function getTabLabel()
    {
        return 'Message Log';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Message Log';
    }

    /**
     * Is this tab hidden?
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Build data for List Message Log
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Messagelog
     */
    protected function _prepareCollection()
    {
        $contractCode = $this->commMessagingHelper->getUom($this->getList()->getData('erp_code'));
        $accountNumber = $this->commMessagingHelper->getSku($this->getList()->getData('erp_code'));
        $collection = $this->commResourceMessageLogCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Message_Log_Collection */
        $collection->addFieldToFilter('message_parent', \Epicor\Comm\Model\Message::MESSAGE_TYPE_UPLOAD);
        $collection->addFieldToFilter('message_category', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_LIST);
        if ($this->getList()->getType() == 'Co') {
            $collection->addFieldToFilter('message_subject', $accountNumber . '-' . $contractCode);
        } else {
            $collection->addFieldToFilter('message_subject', $this->getList()->getErpCode());
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List Message Log
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Messagelog
     */
    protected function _prepareColumns()
    {

        $this->addColumn('message_type', array(
            'header' => __('Message Type'),
            'align' => 'left',
            'index' => 'message_type',
            'renderer' => 'Epicor\Comm\Block\Renderer\Message'
        ));

        $this->addColumn('message_status', array(
            'header' => __('Message Status'),
            'align' => 'left',
            'index' => 'message_status',
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Messagestatus'
        ));

        $this->addColumn('message_subject', array(
            'header' => __('Message Subject'),
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
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array(
                        'base' => 'adminhtml/epicorcomm_message_log/view',
                        'params' => array(
                            'source' => 'list',
                            'sourceid' => $this->getList()->getId())
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Reprocess'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_log/reprocess',
                        'params' => array(
                            'source' => 'list',
                            'sourceid' => $this->getList()->getId()
                        )
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array(
            'source' => 'list',
            'sourceid' => $this->getList()->getId(),
            'id' => $row->getId()
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/view', $params);
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getList()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/messageloggrid', $params);
    }

}
