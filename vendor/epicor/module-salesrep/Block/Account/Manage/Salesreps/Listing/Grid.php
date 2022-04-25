<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Salesreps\Listing;


/**
 * Sales Rep Account Sales Rep List
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        array $data = []
    )
    {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('salesrepGrid');
        $this->setSaveParametersInSession(false);

        $this->setIdColumn('entity_id');
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
    }

    protected function _prepareCollection()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        $salesRep = $helper->getManagedSalesRepAccount();

        $collection = $salesRep->getSalesRepsCollection($this->storeManager->getWebsite()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('salesrep_id', array(
            'header' => __('Sales Rep ID'),
            'width' => '150',
            'index' => 'sales_rep_id',
            'filter_index' => 'sales_rep_id'
        ));

        $this->addColumn('salesrep_name', array(
            'header' => __('Customer'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('salesrep_email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'filter_index' => 'email'
        ));

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        if ($helper->canEdit()) {
            $this->addColumn('action', array(
                'header' => __('Action'),
                'width' => '100',
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
                'links' => 'true',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Unlink'),
                        'url' => array('base' => '*/*/unlinkSalesRep'),
                        'field' => 'salesreps',
                        'confirm' => __('Are you sure you want to unlink this sales rep from the sales rep account?')
                    ),
                    array(
                        'caption' => __('Delete'),
                        'url' => array('base' => '*/*/deleteSalesRep'),
                        'field' => 'salesreps',
                        'confirm' => __('Are you sure you want to delete this sales rep? This action cannot be undone')
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canEdit()) {

            $this->setMassactionIdField('id');
            $this->getMassactionBlock()->setFormFieldName('salesreps');

            $this->getMassactionBlock()->addItem('unlink', array(
                'label' => __('Unlink'),
                'url' => $this->getUrl('*/*/unlinkSalesRep'),
                'confirm' => __('Unlink selected Sales Reps?')
            ));

            $this->getMassactionBlock()->addItem('delete', array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/deleteSalesRep'),
                'confirm' => __('Delete selected Sales Reps? This action cannot be undone')
            ));
        }

        return $this;
    }

    protected function _prepareLayout()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canEdit()) {
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Add'),
                    'id' => 'salesrep_add',
                    //'onclick' => "javascript:\$('sales_rep_add_form').show()",
                    'class' => 'task'
                ))
            );
        }
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        $html = $this->getChildHtml('add_button');
        $html .= '<script type="text/javascript">
        require([\'jquery\'],function($){
            $("#salesrep_add").click(function(){
                $("#sales_rep_add_form").show();
            });
        });
    </script>';
        return  $html;
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
        return false;
    }

    protected function _toHtml()
    {

        $html = parent::_toHtml();

        $html .= '<script>
        var FORM_KEY = "' . $this->getFormKey() . '";
</script>';

        return $html;
    }
}
