<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Attachments;


class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('claim_attachments');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('dealerconnect');
        $this->setMessageType('dcld');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $this->setCustomData(array());
        $this->_emptyText = '';
    }

    protected function _getColumns()
    {
        $columns = array();
        //if ($this->registry->registry('claims_editable') || $this->registry->registry('claims_editable_partial')) {
            $columns['delete'] = array(
                'header' => __('Delete'),
                'align' => 'center',
                'index' => 'delete',
                'type' => 'text',
                'width' => '50px',
                'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Attachments\Renderer\Delete',
                'filter' => false,
                'sortable'  => false
            );
        //}

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Attachments\Renderer\Description',
            'filter' => false,
            'sortable'  => false
        );

        $columns['filename'] = array(
            'header' => __('Filename'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text',
            'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Attachments\Renderer\Filename',
            'filter' => false,
            'sortable'  => false
        );
        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '<div style="display:none">
        <table>
            <tr title="" class="attachments_row" id="claim_attachments_row_template">
                <td class="a-center">
                    <input type="checkbox" name="" class="claimattachments_delete" />
                </td>
                <td class="a-left ">
                    <input type="text" class="claimattachments_description" value="" name="" />
                </td>
                <td class="a-left newattachment">
                    <input type="file" class="claimattachments_filename" name="">
                </td>
            </tr>
        </table>
    </div>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        $extra = $this->registry->registry('claim_new') ? ' new' : '';
        return 'attachments_row' . $extra;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
