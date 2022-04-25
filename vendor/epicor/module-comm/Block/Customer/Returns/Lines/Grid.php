<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_returns_edit';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->commReturnsHelper = $commReturnsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        if (!$this->isReview()) {
            $this->setId('return_lines');
        } else {
            $this->setId('return_lines_review');
        }

        $this->setIdColumn('id');
        $this->setDefaultSort('number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setKeepRowObjectType(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setShowAll(true);

        $lines = array();

        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        if ($return) {
            $linesData = $return->getLines() ?: array();
            foreach ($linesData as $row) {
                $row->setUniqueId(uniqid());
                $row->setRowIdentifier('lines_' . $row->getUniqueId());

                $lines[] = $row;
            }
        }

        $this->setCustomData($lines);
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        if (!$this->isReview()) {

            if ($this->registry->registry('current_return_line')) {
                $this->registry->unregister('current_return_line');
            }

            $this->registry->register('current_return_line', $this->dataObjectFactory->create());

            $block = $this->getLayout()->createBlock('\Epicor\Comm\Block\Customer\Returns\Lines\Attachments');
            /* @var $block Epicor_Comm_Block_Customer_Returns_Lines_Attachments */
            $select = '<select name="" class="return_line_returncode">';

            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            $codes = $customer->getReturnReasonCodes();
            $select .= '<option value="">Please select</option>';
            foreach ($codes as $code => $description) {
                $select .= '<option value="' . $code . '">' . $description . '</option>';
            }
            $select .= '</select>';

            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */

            if ($helper->checkConfigFlag('line_attachments')) {
                $expandCol = '<td class="a-left expand-row " style="cursor: pointer;">
                            <span id="return-line-attachments-" class="plus-minus">+</span>
                        </td>';
            } else {
                $expandCol = '';
            }
            $notesLength = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $maxLength = $notesLength ? 'maxLength=' . $notesLength : '';
            $notesRequired = $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $html .= '
            <div style="display:none">
                <table>
                    <tr title="" class="lines_row" id="return_lines_row_template">
                        ' . $expandCol . '
                        <td class="a-left ">
                            <input type="checkbox" name="delete" class="return_line_delete" />
                        </td>
                        <td class="a-left ">
                            <span class="return_line_number"></span>
                            <input type="hidden" name="configured" value="" class="return_line_configured" />
                            <input type="hidden" name="source_type" value="" class="return_line_source_type" />
                            <input type="hidden" name="source_value" value="" class="return_line_source_value" />
                        </td>
                        <td class="a-left ">
                            <span class="return_sku"></span>
                            <input type="hidden" name="sku" value="" class="return_line_sku" />
                        </td>
                        <td class="a-left ">
                            <span class="return_uom"></span>
                            <input type="hidden" name="uom" value="" class="return_line_uom" />
                        </td>
                        <td class="a-left ">
                            <input type="text" class="return_line_quantity_returned" value="" name="quantity_returned" />
                            <input type="hidden" class="return_line_quantity_ordered" value="" name="quantity_ordered" />
                            <span class="return_line_quantity_ordered_label"></span>
                        </td>
                        <td class="a-left ">
                            ' . $select . '
                        </td>';
            if ($notesRequired) {
                $html .= '<td class="a-center">
                            <textarea class="return_line_notes" ' . $maxLength . ' name="notes"></textarea>';
                if ($notesLength) {
                    $html .= '<div id="truncated_message_line_notes">max ' . $notesLength . ' chars</div>';
                }
                $html .= '</td>';
            }
            $html .= '<td class="a-left ">
                            <span class="return_line_source_label"></span>
                            <input type="hidden" name="source" value="" class="return_line_source" />
                            <input type="hidden" name="source_data" value="" class="return_line_source_data" />
                        </td>
                        <td class="a-left expand-content no-display last"></td>
                    </tr>
                    <tr class="lines_row attachment" id="return_line_attachments_row_template" style="display:none">
                        <td colspan="10" class="attachments-row">'
                . $block->toHtml()
                . '</td>
                    </tr>
                </table>
            </div>';
        }
        return $html;
    }

    protected function _getColumns()
    {
        $return = $this->registry->registry('return_model');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $addAllowed = ($return) ? $return->isActionAllowed('Add') : true;

        $columns = array(
            'expand' => array(
                'header' => ' ',
                'align' => 'left',
                'index' => 'expand',
                'type' => 'text',
                'column_css_class' => "expand-row",
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Expand',
                'filter' => false
            ),
            'delete' => array(
                'header' => __('Delete'),
                'align' => 'left',
                'index' => 'delete',
                'type' => 'text',
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Delete',
                'filter' => false,
            ),
            'entity_id' => array(
                'header' => __('Line'),
                'align' => 'left',
                'index' => 'number',
                'type' => 'number',
                'sortable' => false,
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Number',
            ),
            'product_code' => array(
                'header' => __('SKU'),
                'align' => 'left',
                'index' => 'product_code',
                'type' => 'text',
                'sortable' => false,
            ),
            'unit_of_measure_code' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_code',
                'type' => 'text',
                'sortable' => false,
            ),
            'qty' => array(
                'header' => __('Qty'),
                'align' => 'left',
                'index' => 'qty',
                'type' => 'text',
                'sortable' => false,
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Qty',
            ),
            'return_code' => array(
                'header' => __('Return Code'),
                'align' => 'left',
                'index' => 'return_code',
                'type' => 'text',
                'sortable' => false,
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Returncode',
            ),
        );

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT) &&
            $this->scopeConfig->getValue('epicor_comm_returns/notes/line_notes_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $columns['notes_text'] = array(
                'header' => __('Notes'),
                'align' => 'left',
                'index' => 'note_text',
                'type' => 'text',
                'sortable' => false,
                'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Notes'
            );
        }
        $columns['source'] = array(
            'header' => __('Source'),
            'align' => 'left',
            'index' => 'source',
            'type' => 'text',
            'sortable' => false,
            'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Source',
        );
        $columns['attachments'] = array(
            'header' => ($this->isReview()) ? __('Attachments') : '',
            'align' => 'left',
            'index' => 'attachments',
            'renderer' => 'Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Attachments',
            'type' => 'text',
            'filter' => false,
            'keep_data_format' => 1,
            'column_css_class' => (!$this->isReview()) ? 'no-display' : '',
            'header_css_class' => (!$this->isReview()) ? 'no-display' : ''
        );

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if (!$helper->checkConfigFlag('line_attachments')) {
            unset($columns['expand']);
            unset($columns['attachments']);
        }

        if ($this->isReview()) {
            unset($columns['expand']);
            unset($columns['delete']);
        } else {
            if (!$addAllowed) {
                unset($columns['delete']);
            }
        }

        return $columns;
    }

    public function getRowClass($row)
    {
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */
        $class = 'lines_row';

        if ($row->getToBeDeleted() == 'Y' && $this->isReview()) {
            $class .= ' deleting';
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
