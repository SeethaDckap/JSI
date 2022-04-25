<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab;


/**
 * Product Message Log Grid
 *
 * @author David.Wylie
 */
class Log extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

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
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Epicor\Comm\Block\Renderer\MessageFactory $commRendererMessageFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\MessagestatusFactory $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory,
        array $data = []
    )
    {
        $this->commRendererMessageFactory = $commRendererMessageFactory;
        $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory = $commAdminhtmlWidgetGridColumnRendererMessagestatusFactory;
        $this->registry = $registry;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('product_message_log');
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

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

    protected function _getProduct()
    {
        if (!$this->registry->registry('current_product')) {
            $this->registry->register('current_product', $this->catalogProductFactory->create()->load($this->getRequest()->getParam('id')));
        }
        return $this->registry->registry('current_product');
    }

    protected function _prepareCollection()
    {
        $sku = $this->_getProduct()->getSku();
        $collection = $this->commResourceMessageLogCollectionFactory->create();


        /* @var $collection Epicor_Comm_Model_Resource_Erp_Customer_Sku_Collection */
        $collection->addFieldToFilter('message_parent', \Epicor\Comm\Model\Message::MESSAGE_TYPE_UPLOAD);
        $collection->addFieldToFilter('message_category', \Epicor\Comm\Model\Message::MESSAGE_CATEGORY_PRODUCT);
        $collection->addFieldToFilter('message_subject', $sku);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_type', array(
            'header' => __('Message Type'),
            'align' => 'left',
            'index' => 'message_type',
            'renderer' => $this->commRendererMessageFactory->create(),
        ));

        $this->addColumn('message_status', array(
            'header' => __('Message Status'),
            'align' => 'left',
            'index' => 'message_status',
            'renderer' => $this->commAdminhtmlWidgetGridColumnRendererMessagestatusFactory->create()
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
                            'source' => 'product',
                            'sourceid' => $this->_getProduct()->getId()
                        )
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Reprocess'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_log/reprocess',
                        'params' => array(
                            'source' => 'product',
                            'sourceid' => $this->_getProduct()->getId()
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
//        $this->addExportType('*/*/exportCsv', Mage::helper('epicor_comm')->__('CSV'));  removed export buttons until problems resolved 
//        $this->addExportType('*/*/exportXml', Mage::helper('epicor_comm')->__('XML'));  have not removed actions from ErpaccountController

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        $params = array(
            'source' => 'product',
            'sourceid' => $this->_getProduct()->getId(),
            'id' => $row->getId(),
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/view', $params);
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->_getProduct()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_message_log/grid', $params);
    }

    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/epicorcomm_message_log/grid', array('_current' => true));
    }

    public function getSkipGenerateContent()
    {
        return false;
    }

    public function getTabClass()
    {
        return 'ajax notloaded';
    }

}
