<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Viewattachments;


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
        
        $claim = $this->registry->registry('dealer_connect_claim_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($claim) {
            $attData = ($claim->getAttachments()) ? $claim->getAttachments()->getasarrayAttachment() : array();
            $attachments = array();

            // add a unique id so we have a html array key for these things
            foreach ($attData as $row) {
                $row->setUniqueId(uniqid());
                $attachments[] = $row;
            }

            $this->setCustomData($attachments);
        }
    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Viewattachments\Renderer\Description',
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
            <tr title="" class="attachments_row" id="attachments_row_template">
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
