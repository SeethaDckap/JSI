<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments;


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

        $this->setId('rfq_attachments');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqd');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $rfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($rfq) {
            $attData = ($rfq->getAttachments()) ? $rfq->getAttachments()->getasarrayAttachment() : array();
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
        if ($this->registry->registry('rfqs_editable') || $this->registry->registry('rfqs_editable_partial')) {
            $columns['delete'] = array(
                'header' => __('Delete'),
                'align' => 'center',
                'index' => 'delete',
                'type' => 'text',
                'width' => '50px',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Delete',
                'filter' => false
            );
        }

        $columns['description'] = array(
            'header' => __('Description'),
            'align' => 'left',
            'index' => 'description',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Description',
            'filter' => false
        );

        $columns['filename'] = array(
            'header' => __('Filename'),
            'align' => 'left',
            'index' => 'filename',
            'type' => 'text',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Filename',
            'filter' => false
        );
        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        if ($this->scopeConfig->getValue('customerconnect_enabled_messages/CRQD_request/attachment_support', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
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
        } else {
            $html = '';
        }
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        $extra = $this->registry->registry('rfq_new') ? ' new' : '';
        return 'attachments_row' . $extra;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
