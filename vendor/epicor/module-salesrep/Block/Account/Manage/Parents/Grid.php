<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Parents;


/**
 * Sales Rep Account Hierarchy Parents List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        array $data = [])
    {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;

        parent::__construct($context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data);

        $this->setId('sales_rep_account_parents');

        $this->setIdColumn('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('epicor_salesrep');

        $this->setKeepRowObjectType(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setShowAll(true);

        $parents = array();

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        $salesRep = $helper->getManagedSalesRepAccount();
        $baseAccount = $helper->getBaseSalesRepAccount();

        if ($salesRep) {
        $parents = $salesRep->getParentAccounts();
        }

        if ($helper->isManagingChild()) {
        $parentIds = $salesRep->getParentAccounts(true);
        if (in_array($baseAccount->getId(), $parentIds)) {
        $this->setHideActionColumn(true);
        }
        }

        $this->setCustomData($parents);
        $this->setCustomColumns($this->_getColumns());

    }


    protected function _getColumns()
    {
        $columns = array();

        $columns['parent_sales_rep_id'] = array(
            'header' => __('Sales Rep Account Number'),
            'align' => 'left',
            'width' => '100',
            'index' => 'sales_rep_id',
            'filter_index' => 'sales_rep_id',
        );

        $columns['parent_name'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
        );

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->isManagingChild() && !$this->getHideActionColumn()) {
            $columns['action'] = array(
                'header' => __('Action'),
                'width' => '100',
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
                'links' => 'true',
                'getter' => 'getEncodedId',
                'actions' => array(
                    array(
                        'caption' => __('Manage'),
                        'url' => array('base' => '*/*/manage'),
                        'field' => 'salesrepaccount'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            );
        }

        return $columns;
    }

    /**
     * 
     * @param \Epicor\SalesRep\Model\Account $row
     * @return string
     */
    public function getRowUrl($row)
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->isManagingChild()) {
            return $this->getUrl('*/*/manage', array('salesrepaccount' => $helper->encodeId($row->getId())));
        } else {
            return false;
        }
    }

    public function toHtml()
    {
        $html = parent::toHtml();

        $html .= '<script>
        var FORM_KEY = "'.$this->getFormKey().'";
</script>';

        return $html;
    }

}
