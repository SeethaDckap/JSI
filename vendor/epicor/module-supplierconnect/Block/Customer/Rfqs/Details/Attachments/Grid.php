<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments;

use Epicor\Common\Model\Message\CollectionFactory;
use Epicor\Common\Helper\Data as CommonHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Registry;

/**
 * Supplierconnect attachments grid.
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * Registry class.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Scope config interface.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * Constructor function
     *
     * @param Context           $context                        Context class.
     * @param Data              $backendHelper                  Backend helper.
     * @param CollectionFactory $commonMessageCollectionFactory Message collection factory.
     * @param CommonHelper      $commonHelper                   Common helper class.
     * @param UrlHelper         $frameworkHelperDataHelper      Url helper.
     * @param Registry          $registry                       Registry class.
     * @param array             $data                           Data.
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $commonMessageCollectionFactory,
        CommonHelper $commonHelper,
        UrlHelper $frameworkHelperDataHelper,
        Registry $registry,
        array $data=[]
    ) {
        $this->registry    = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('supplier_rfq_attachments');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('supplierconnect');
        $this->setMessageType('surd');
        $this->setIdColumn('number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($rfq) {
            $attData     = ($rfq->getAttachments()) ? $rfq->getAttachments()->getasarrayAttachment() : array();
            $attachments = [];

            // Add a unique id so we have a html array key for these things.
            foreach ($attData as $row) {
                $row->setUniqueId(uniqid());
                $attachments[] = $row;
            }

            $this->setCustomData($attachments);
        }

    }//end __construct()


    /**
     * Get columns.
     *
     * @return array
     */
    protected function _getColumns()
    {
        $columns = [];
        if ($this->registry->registry('rfq_editable')) {
            $columns['delete'] = [
                'header'   => __('Delete'),
                'align'    => 'center',
                'index'    => 'delete',
                'type'     => 'text',
                'width'    => '50px',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Delete',
                'filter'   => false,
            ];
        }

        $columns['description'] = [
            'header'   => __('Description'),
            'align'    => 'left',
            'index'    => 'description',
            'type'     => 'text',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Description',
            'filter'   => false,
        ];

        $columns['filename'] = [
            'header'   => __('Filename'),
            'align'    => 'left',
            'index'    => 'filename',
            'type'     => 'text',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer\Filename',
            'filter'   => false,
        ];
        return $columns;

    }//end _getColumns()


    /**
     * Render html.
     *
     * @return string
     */
    public function _toHtml()
    {
        $html = parent::_toHtml();
        if (true) {
            $html .= '<div style="display:none">
            <table>
                <tr title="" class="attachments_row" id="attachments_row_template">
                    <td class="a-center">
                        <input type="checkbox" value="1" name="template_attachments[][delete]" class="attachments_delete"/>
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

    }//end _toHtml()


    /**
     * Get row class.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function getRowClass(DataObject $row)
    {
        return 'attachments_row';

    }//end getRowClass()


    /**
     * Get row url.
     *
     * @param mixed $row Row object.
     *
     * @return string|void|null
     */
    public function getRowUrl($row)
    {
        return null;

    }//end getRowUrl()


}//end class
