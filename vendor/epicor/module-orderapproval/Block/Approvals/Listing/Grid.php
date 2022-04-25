<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Approvals\Listing;

use Epicor\OrderApproval\Model\GroupManagement as GroupManagement;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;
use Epicor\OrderApproval\Model\Approval\OrderApprovals;
use Epicor\OrderApproval\Model\Approval\ApprovedStatus;

/**
 * Class Grid
 * @package Epicor\OrderApproval\Block\Group\Listing
 */
class Grid extends \Epicor\Common\Block\Widget\Grid\Extended
{
    const FRONTEND_RESOURCE_EXPORT = 'Epicor_Customer::my_account_approvals_export';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customer::my_account_approvals_details';

    const FRONTEND_RESOURCE_APPROVED_REJECT = 'Epicor_Customer::my_account_approvals_approved_reject';

    /**
     * @var string
     */
    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';
    
    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var GroupUtilities
     */
    private $groupUtilities;

    /**
     * @var OrderApprovals
     */
    private $orderApprovals;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    private $accessauthorization;

    /**
     * @var ApprovedStatus
     */
    private $approvedStatus;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param GroupManagement $groupManagement
     * @param GroupUtilities $groupUtilities
     * @param OrderApprovals $orderApprovals
     * @param ApprovedStatus $approvedStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        GroupManagement $groupManagement,
        GroupUtilities $groupUtilities,
        OrderApprovals $orderApprovals,
        ApprovedStatus $approvedStatus,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('approval-grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setNoFilterMassactionColumn(false);

        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => 'epicor_orderapproval/manage/exportCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => 'epicor_orderapproval/manage/exportXml'));
        $this->groupManagement = $groupManagement;
        $this->groupUtilities = $groupUtilities;
        $this->orderApprovals = $orderApprovals;
        $this->accessauthorization = $context->getAccessAuthorization();
        $this->approvedStatus = $approvedStatus;
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->accessauthorization->isAllowed($code);
    }

    public function getExportTypes()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_EXPORT)) {
            return false;
        }

        return parent::getExportTypes();
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->approvedStatus->getApprovalHistoryCollection());

        return parent::_prepareCollection();
    }

    /**
     * @return void
     */
    private function setExportFields()
    {
        if ($exportCsv = $this->getExportTypeCsv()) {
            $this->addExportType(@$exportCsv['url'], __(@$exportCsv['text']));
        }

        if ($exportXml = $this->getExportTypeXml()) {
            $this->addExportType(@$exportXml['url'], __(@$exportXml['text']));
        }
    }

    /**
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->setExportFields();
        if($this->isApprovedRejectAllowed()) {
            $this->addColumn('approve', [
                'header' => __('Approve'),
                'index' => 'entity_id',
                'class' => 'approve-check',
                'sortable' => true,
                'filter' => false,
                'disabled_values' => $this->getDisabledApproveReject(),
                'is_system' => true,
                'type' => 'checkbox'
            ]);
            $this->addColumn('reject', [
                'header' => __('Reject'),
                'index' => 'entity_id',
                'class' => 'reject-check',
                'sortable' => true,
                'filter' => false,
                'is_system' => true,
                'disabled_values' => $this->getDisabledApproveReject(),
                'type' => 'checkbox'
            ]);
        }

        $this->addColumn('increment_id', [
            'header' => __('Order#'),
            'index' => 'increment_id',
            'sortable' => true,
            'type' => 'text'
        ]);
        $this->addColumn('created_at', [
            'header' => __('Date'),
            'index' => 'created_at',
            'sortable' => true,
            'renderer' => 'Epicor\OrderApproval\Block\Approvals\Renderer\Date',
            'type' => 'date'
        ]);
        $this->addColumn('customer_firstname', [
            'header' => __('Requestor'),
            'index' => 'customer_firstname',
            'renderer' => 'Epicor\OrderApproval\Block\Approvals\Renderer\Requestor',
            'sortable' => true,
            'type' => 'text'
        ]);
        $this->addColumn('ship_to', [
            'header' => __('Ship To'),
            'index' => 'customer_firstname',
            'sortable' => true,
            'renderer' => 'Epicor\OrderApproval\Block\Approvals\Renderer\ShipTo',
            'type' => 'text'
        ]);
        $this->addColumn('order_total', [
            'header' => __('Order Total'),
            'index' => 'grand_total',
            'sortable' => true,
            'type' => 'currency'
        ]);
        $this->addColumn('history_status', [
            'header' => __('Status'),
            'index' => 'history_status',
            'sortable' => true,
            'filter_index' => 'tmp.status',
            'renderer' => 'Epicor\OrderApproval\Block\Approvals\Renderer\Status',
            'options' => [
                'Approved' =>'Approved',
                'Rejected' => 'Rejected',
                'Pending' => 'Pending'
            ],
            'type' => 'options'
        ]);
        $this->addColumn('history_data', [
            'header' => __('Created By'),
            'index' => 'id',
            'type' => 'hidden',
            'name' => 'test',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ]);

        $this->addColumn('view_order', [
            'header' => __('Action'),
            'column_css_class' => $this->isDetailActionAllowed() ? 'action-link-ht':'no-display',
            'header_css_class' => $this->isDetailActionAllowed() ? 'action-link-ht':'no-display',
            'sortable' => false,
            'filter' => false,
            'is_system' => true,
            'renderer' => 'Epicor\OrderApproval\Block\Approvals\Renderer\View',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Access Rights show hide detail action.
     *
     * @return bool
     */
    public function isDetailActionAllowed()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isApprovedRejectAllowed()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_APPROVED_REJECT)) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    private function getDisabledApproveReject()
    {
       return $this->approvedStatus->getApprovalOrdersNotPending();
    }

    /**
     * Overcomes issue magento setOrder results in sql error when using field 'order'
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return Grid
     */
    protected function _setCollectionOrder($column)
    {
        $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $dir = $column->getDir();
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        $collection = $this->getCollection();
        if ($columnIndex === 'order' && $collection && $dir) {
            $collection->getSelect()->order("main_table.order $dir");
        } else {
            return parent::_setCollectionOrder($column);
        }
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/myapprovalgrid', array(
            '_current' => true
        ));
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function _statusFilter($collection, $column)
    {
        if (!$column->getFilter()->getValue()) {
            return $this;
        }

        return $this;
    }

    /**
     * Add column filtering conditions to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $filterCondition = $column->getFilter()->getCondition();
                //Takes into account self approved status
                if ($this->isFilterApprovedStatus($field, $filterCondition)) {
                    $condition = ['in' => ['Approved', 'Self Approved']];
                } else {
                    $condition = $column->getFilter()->getCondition();
                }
                if ($field && isset($condition)) {
                    $this->getCollection()->addFieldToFilter($field, $condition);
                }
            }
        }
        return $this;
    }

    /**
     * @param $field
     * @param $filterCondition
     * @return bool
     */
    private function isFilterApprovedStatus($field, $filterCondition)
    {
        return $field === 'tmp.status' && isset($filterCondition['eq']) && $filterCondition['eq'] === 'Approved';
    }

}
