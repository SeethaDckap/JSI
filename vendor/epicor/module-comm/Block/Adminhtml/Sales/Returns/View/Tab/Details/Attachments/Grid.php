<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details\Attachments;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $_defaultLimit = 10000;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory
     */
    protected $commResourceCustomerReturnModelAttachmentCollectionFactory;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;
    /*
     *@var \Epicor\Common\Model\ResourceModel\File\CollectionFactory
     */
    protected $fileCollectionFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Backend\Helper\Data $backendHelper,
    \Magento\Framework\Registry $registry,
    \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory $commResourceCustomerReturnModelAttachmentCollectionFactory,
    \Epicor\Common\Model\FileFactory $commonFileFactory,
    \Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\DescriptionFactory $commCustomerReturnsAttachmentLinesRendererDescriptionFactory,
    \Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\FileFactory $commCustomerReturnsAttachmentLinesRendererFileFactory,
    \Epicor\Common\Model\ResourceModel\File\CollectionFactory  $fileCollectionFactory,      
    array $data = []
    ) {
        $this->commCustomerReturnsAttachmentLinesRendererDescriptionFactory = $commCustomerReturnsAttachmentLinesRendererDescriptionFactory;
        $this->commCustomerReturnsAttachmentLinesRendererFileFactory = $commCustomerReturnsAttachmentLinesRendererFileFactory;
        $this->registry = $registry;
        $this->commResourceCustomerReturnModelAttachmentCollectionFactory = $commResourceCustomerReturnModelAttachmentCollectionFactory;
        $this->commonFileFactory = $commonFileFactory;
        $this->fileCollectionFactory = $fileCollectionFactory;
        
        parent::__construct(
                $context, $backendHelper, $data
        );
        $this->setId('attachments');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection() {
        $return = $this->registry->registry('return');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        $attachmentIds = array();
        
        $attData = $this->commResourceCustomerReturnModelAttachmentCollectionFactory->create();
        /* @var $attData Epicor_Comm_Model_Resource_Customer_Return_Attachment_Collection */
        $attData->addFieldToFilter('return_id', array('eq' => $return->getId()));
        $attData->addFieldToFilter('line_id', array('null' => true));
        
        foreach ($attData->getItems() as $row) {
//            $attachment = $this->commonFileFactory->create()->load($row->getAttachmentId());
//            /* @var $attachment Epicor_Common_Model_File */
//            $attachment->setAttachmentLink($row);
//            $attData->removeItemByKey($row->getId());
//            $attData->addItem($attachment);
            $attachmentIds[] = $row->getAttachmentId();
        }
        
        $attachmentCollection = $this->fileCollectionFactory->create();
        $attachmentCollection->addFieldToFilter('id', array('in' => $attachmentIds));
        
        
        $this->setCollection($attachmentCollection);

        return parent::_prepareCollection();
    }

    public function getRowUrl($row) {
        return false;
    }

    protected function _prepareColumns() {

        $columns = array();

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => '\Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\Description',
            'filterable' => false,
            'sortable' => false,
        );

        $columns['filename'] = array(
            'header' => __('Filename'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text',
            'renderer' => '\Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\File',
            'filterable' => false,
            'sortable' => false,
        );


        $this->addColumn('description', $columns['description']);
        $this->addColumn('filename', $columns['filename']);

        parent::_prepareColumns();
    }

}
