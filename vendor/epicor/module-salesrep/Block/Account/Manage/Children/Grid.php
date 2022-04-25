<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Children;


/**
 * Sales Rep Account Hierarchy Children List
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
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = [])
    {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;

        parent::__construct($context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data);

        $this->setId('sales_rep_account_children');

        $this->setIdColumn('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('epicor_salesrep');
        $this->setCustomColumns($this->_getColumns());
        $this->setKeepRowObjectType(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setShowAll(true);

        $children = array();

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $salesRep = $helper->getManagedSalesRepAccount();

        if ($salesRep) {
            $children = $salesRep->getChildAccounts();
        }

        $this->setCustomData($children);

    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['child_sales_rep_id'] = array(
            'header' => __('Sales Rep Account Number'),
            'align' => 'left',
            'width' => '100',
            'index' => 'sales_rep_id',
            'filter_index' => 'sales_rep_id',
        );

        $columns['child_name'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
        );

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
                ),
                array(
                    'caption' => __('Unlink'),
                    'url' => array('base' => '*/*/unlinkchildaccount'),
                    'confirm' => __('Are you sure you want to unlink this child account from the sales rep account?'),
                    'field' => 'salesrepaccount'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        );

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

        return $this->getUrl('*/*/manage', array('salesrepaccount' => $helper->encodeId($row->getId())));
    }

}
