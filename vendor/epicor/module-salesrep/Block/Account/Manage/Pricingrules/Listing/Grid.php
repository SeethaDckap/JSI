<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Pricingrules\Listing;


/**
 * Sales Rep Account Pricing Rules List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory
     */
    protected $salesRepResourcePricingRuleCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory $salesRepResourcePricingRuleCollectionFactory,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        array $data = []
    )
    {
        $this->salesRepResourcePricingRuleCollectionFactory = $salesRepResourcePricingRuleCollectionFactory;
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('pricing_rules');
        $this->setSaveParametersInSession(false);
        $this->setRowInitCallback('pricingRules.rowInit.bind(pricingRules)');
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourcePricingRuleCollectionFactory->create();
        /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Collection */

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $salesRepAccount = $helper->getManagedSalesRepAccount();

        $collection->addFieldToFilter('sales_rep_account_id', $salesRepAccount->getId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('rule_name', array(
            'header' => __('Name'),
            'index' => 'name',
            'filter_index' => 'name'
        ));
        
        $this->addColumn('from_date', array(
            'type' => 'date',
            'header' => __('Start Date'),
            'index' => 'from_date',
            'filter_index' => 'from_date',
            'renderer' => 'Epicor\SalesRep\Block\Widget\Grid\Column\Renderer\Date' 
        ));

        $this->addColumn('to_date', array(
            'type' => 'date',
            'header' => __('Expiry Date'),
            'index' => 'to_date',
            'filter_index' => 'to_date',
            'renderer' => 'Epicor\SalesRep\Block\Widget\Grid\Column\Renderer\Date'
        ));
        
        $this->addColumn('is_active', array(
            'type' => 'options',
            'header' => __('Status'),
            'index' => 'is_active',
            'filter_index' => 'is_active',
            'options' => array(
                '1' => 'Active',
                '0' => 'Inactive'
            ),
        ));

        $this->addColumn('priority', array(
            'type' => 'number',
            'header' => __('Priority Order'),
            'index' => 'priority',
            'filter_index' => 'priority'
        ));

        $this->addColumn('action_operator', array(
            'type' => 'options',
            'header' => __('Action Price Base'),
            'index' => 'action_operator',
            'filter_index' => 'action_operator',
            'options' => array(
                'cost' => '% above Cost Price',
                'list' => '% below Customer Price',
                'base' => '% below Base Price',
            ),
        ));

        $this->addColumn('action_amount', array(
            'type' => 'number',
            'header' => __('Margin %'),
            'index' => 'action_amount',
            'filter_index' => 'action_amount'
        ));

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        if ($helper->canEdit()) {
            $this->addColumn('action', array(
                'header' => __('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Delete'),
                        'url' => array('base' => '*/*/deletepricingrule'),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'id',
                'is_system' => true,
            ));
        }

        $this->addColumn('conditions', array(
            'header' => __('Conditions'),
            'name' => 'conditions',
            'renderer' => 'Epicor\SalesRep\Block\Adminhtml\Widget\Grid\Column\Renderer\Conditions',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        $this->addColumn('rowdata', array(
            'header' => __('Row'),
            'align' => 'left',
            'name' => 'rowdata',
            'width' => 0,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareLayout()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canEdit()) {
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setData(array(
                        'label' => __('Add'),
                        'onclick' => 'pricingRules.add()',
                        'class' => 'task'
                    ))
            );
        }
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getMainButtonsHtml()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canEdit()) {
            $html = $this->getAddButtonHtml();
            $html .= parent::getMainButtonsHtml();
            return $html;
        } else {
            return parent::getMainButtonsHtml();
        }
    }

    public function getRowUrl($row)
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        return "javascript:pricingRules.rowEdit(this, " . $row->getId() . ");";
    }

}
