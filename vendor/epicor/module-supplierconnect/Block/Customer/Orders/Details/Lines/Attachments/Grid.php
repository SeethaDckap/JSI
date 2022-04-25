<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments;


/**
 * Order line attachments grid
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $order = $this->registry->registry('current_order_row');
        /* @var $order \Epicor\Common\Model\Xmlvarien */

        $this->setId('order_line_attachments_' . $order->getUniqueId());
        $this->setClass('order_line_attachments');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('supplierconnect');
        $this->setMessageType('spod');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);


        $attData = ($order->getAttachments()) ? $order->getAttachments()->getasarrayAttachment() : array();
        $attachments = array();

        // add a unique id so we have a html array key for these things
        foreach ($attData as $row) {
            $row->setUniqueId(uniqid());
            $attachments[] = $row;
        }

        $this->setCustomData($attachments);
    }

    /**
     * @return array
     */
    protected function _getColumns()
    {
        $columns = array();

        $columns['delete'] = array(
            'header' => __('Delete'),
            'align' => 'center',
            'index' => 'delete',
            'type' => 'text',
            'width' => '50px',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments\Renderer\Delete',
            'filter' => false
        );

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments\Renderer\Description',
            'filter' => false
        );

        $columns['filename'] = array(
            'header' => __('Filename'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Attachments\Renderer\Filename',
            'filter' => false
        );

        return $columns;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $html = parent::_toHtml();

        $order = $this->registry->registry('current_order_row');
        /* @var $order \Epicor\Common\Model\Xmlvarien */

        $html .= '<div style="display:none">
            <table>
                <tr title="" class="line_attachment_row" id="line_attachment_row_template_' . $order->getUniqueId() . '">
                    <td class="a-center">
                        <input type="checkbox" name="" class="line_attachments_delete" />
                    </td>
                    <td class="a-left ">
                        <input type="text" class="line_attachments_description" value="" name="" />
                    </td>
                    <td class="a-left newattachment">
                        <input type="file" class="line_attachments_filename" name="">
                    </td>
                </tr>
            </table>
        </div>';
        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string|void|null
     */
    public function getRowUrl($row)
    {
        return null;
    }

}
