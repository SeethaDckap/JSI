<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Attachments;


/**
 * Return line attachments grid
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\FileFactory
     */
    protected $commonFileFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\DeleteFactory
     */
    protected $commCustomerReturnsLinesAttachmentsRendererDeleteFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\DescriptionFactory
     */
    protected $commCustomerReturnsLinesAttachmentsRendererDescriptionFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\FileFactory
     */
    protected $commCustomerReturnsLinesAttachmentsRendererFileFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\DeleteFactory $commCustomerReturnsLinesAttachmentsRendererDeleteFactory,
        \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\DescriptionFactory $commCustomerReturnsLinesAttachmentsRendererDescriptionFactory,
        \Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\FileFactory $commCustomerReturnsLinesAttachmentsRendererFileFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->commCustomerReturnsLinesAttachmentsRendererDeleteFactory = $commCustomerReturnsLinesAttachmentsRendererDeleteFactory;
        $this->commCustomerReturnsLinesAttachmentsRendererDescriptionFactory = $commCustomerReturnsLinesAttachmentsRendererDescriptionFactory;
        $this->commCustomerReturnsLinesAttachmentsRendererFileFactory = $commCustomerReturnsLinesAttachmentsRendererFileFactory;
        $this->registry = $registry;
        $this->commonFileFactory = $commonFileFactory;
       parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $line = $this->registry->registry('current_return_line');
        /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

        if (!$this->registry->registry('review_display')) {
            $this->setId('return_line_attachments_' . $line->getUniqueId());
        } else {
            $this->setId('return_line_attachments_' . $line->getUniqueId() . '_review');
        }
        $this->setClass('return_line_attachments');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_comm');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $attData = $line->getAttachments() ?: array();

        $attachments = array();

        // add a unique id so we have a html array key for these things
        foreach ($attData as $row) {
            $attachment = $this->commonFileFactory->create()->load($row->getAttachmentId());
            /* @var $attachment Epicor_Common_Model_File */
            $row->setUniqueId(uniqid());
            $row->setAttachmentModel($attachment);
            $attachments[] = $row;
        }

        $this->setCustomData($attachments);
    }

    protected function _getColumns()
    {
        $line = $this->registry->registry('current_return_line');
        /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

        $allowed = ($line instanceof \Epicor\Comm\Model\Customer\ReturnModel\Line) ? $line->isActionAllowed('Attachments') : true;

        $columns = array(
            'delete' => array(
                'header' => __('Delete'),
                'align' => 'center',
                'index' => 'delete',
                'type' => 'text',
                'width' => '50px',
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\Delete',
                'filter' => false,
                'sortable' => false,
                'column_css_class' => (!$allowed) ? 'no-display' : '',
                'header_css_class' => (!$allowed) ? 'no-display' : ''
            ),
            'description' => array(
                'header' => __('Description'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text',
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\Description',
                'filter' => false,
                'sortable' => false
            ),
            'filename' => array(
                'header' => __('Filename'),
                'align' => 'left',
                'index' => 'filename',
                'type' => 'text',
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Attachments\Renderer\File',
                'filter' => false,
                'sortable' => false
            )
        );

        if ($this->registry->registry('review_display')) {
            unset($columns['delete']);
        }

        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();

        if (!$this->registry->registry('review_display')) {
            $line = $this->registry->registry('current_return_line');
            /* @var $line Epicor_Comm_Model_Customer_ReturnModel_Line */

            $html .= '<div style="display:none">
                <table>
                    <tr title="" class="attachments_row" id="return_line_attachments_' . $line->getUniqueId() . '_attachment_row_template">
                        <td class="a-center">
                            <input type="checkbox" name="" class="attachments_delete" />
                        </td>
                        <td class="a-left ">
                            <input type="text" class="attachments_description" value="" name="" />
                        </td>
                        <td class="a-left newattachment">
                            <input type="file" class="attachments_filename" name="">
                        </td>
                    </tr>
                </table>
            </div>';
        }
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        return 'attachments_row';
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
