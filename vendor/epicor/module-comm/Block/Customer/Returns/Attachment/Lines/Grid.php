<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Attachment\Lines;


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

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
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

        if (!$this->isReview()) {
            $this->setId('customer_returns_attachment_lines');
        } else {
            $this->setId('return_attachments_review');
        }

        $this->setIdColumn('id');
        $this->setDefaultSort('filename');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setShowAll(true);
        $this->setKeepRowObjectType(true);

        $attachments = array();

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        if ($return) {
            $attData = $return->getAttachments() ?: array();
            foreach ($attData as $row) {
                $attachment = $this->commonFileFactory->create()->load($row->getAttachmentId());
                /* @var $attachment Epicor_Common_Model_File */
                $row->setUniqueId(uniqid());
                $row->setAttachmentModel($attachment);
                $attachments[] = $row;
            }
        }

        $this->setCustomData($attachments);

        if ($this->isReview()) {
            $this->_emptyText = __('No Attachments added');
        }
    }

    protected function _getColumns()
    {
        $columns = array();

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
        $allowed = ($return) ? $return->isActionAllowed('Attachments') : true;

        if (!$this->isReview() && $allowed) {
            $columns['delete'] = array(
                'header' => __('Delete'),
                'align' => 'center',
                'index' => 'delete',
                'type' => 'text',
                'width' => '50px',
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\Delete',
                'filter' => false,
                'sortable' => false,
            );
        }

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => '\Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\Description',
            'filter' => false,
            'sortable' => false,
        );

        $columns['filename'] = array(
            'header' => __('Filename'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text',
            'renderer' => '\Epicor\Comm\Block\Customer\Returns\Attachment\Lines\Renderer\File',
            'filter' => false,
            'sortable' => false,
        );

        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();

        if (!$this->isReview()) {
            $html .= '<div style="display:none">
            <table>
                <tr title="" class="attachments_row" id="customer_returns_attachment_lines_attachment_row_template">
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

    public function getRowClass($row)
    {
        /* @var $row Epicor_Common_Model_File */
        $class = 'attachments_row';

        if ($this->isReview()) {
            $return = $this->registry->registry('return_model');
            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */
            $link = $return->getAttachmentLink($row->getId());
            /* @var $link Epicor_Comm_Model_Customer_ReturnModel_Attachment */
            if ($link && $link->getToBeDeleted() == 'Y') {
                $class .= ' deleting';
            }
        }
        return $class;
    }

    public function getRowUrl($row)
    {
        return null;
    }

    private function isReview()
    {
        return $this->registry->registry('review_display');
    }

}
