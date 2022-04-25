<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickup\Select;


/**
 * Branchpickup select page grid
 *
 * @category   Epicor
 * @package    Epicor_Branchpickup
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();
    private $_erpAccount;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer\StreetFactory
     */
    protected $branchPickupPickupsearchSelectRendererStreetFactory;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationHelper;



    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer\StreetFactory $branchPickupPickupsearchSelectRendererStreetFactory,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        array $data = []
    )
    {
        $this->branchPickupPickupsearchSelectRendererStreetFactory = $branchPickupPickupsearchSelectRendererStreetFactory;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->messageManager = $messageManager;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->responseFactory = $responseFactory;
        $this->locationHelper = $this->branchPickupHelper->getLocationHelper();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('selectgrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->redirectSalesRep();
        $this->checkActive();
    }

    protected function _prepareLayout()
    {

        $urlRedirect = $this->getUrl('*/*/removebranchpickup', array(
            '_current' => true,
            'contract' => $this->getRequest()->getParam('contract')
        ));

        if ($this->locationHelper->getLocationStyle() != 'inventory_view') {
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(array(
                'label' => __('Remove Selected Branch Pickup'),
                'class' => 'remove_branch',
            )));
        }

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Mage_Adminhtml_Block_Widget_Grid
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAddButtonHtml();
        return $html;
    }

    /**
     * Redirect User If the Masquerade account was not selected
     */
    public function redirectSalesRep()
    {
        $helper = $this->branchPickupHelper;
        /* @var Epicor_BranchPickup_Helper_Data */
        $isSalesRepSelected = $helper->salesRepRedirect();
        if ($isSalesRepSelected) {
            $this->messageManager->addNoticeMessage('Please select a Masquerade account');
            //M1 > M2 Translation Begin (Rule p2-6.2)
            //Mage::app()->getFrontController()->getResponse()->setRedirect($isSalesRepSelected);
            $this->responseFactory->create()->setRedirect($isSalesRepSelected)->sendResponse();
            //M1 > M2 Translation End
        }
    }

    public function checkActive()
    {
        $helper = $this->branchPickupHelper;
        /* @var Epicor_BranchPickup_Helper_Data */
        $branchPickupActive = $helper->branchPickupActive();
        if (!$branchPickupActive) {
            $this->messageManager->addNoticeMessage('You are not authorized to access this page');
            //M1 > M2 Translation Begin (Rule p2-6.2)
            //Mage::app()->getFrontController()->getResponse()->setRedirect('/');
            $this->responseFactory->create()->setRedirect('/')->sendResponse();
            //M1 > M2 Translation End
        }
    }

    /**
     * Build data for List Locations
     */
    protected function _prepareCollection()
    {
        $locationIds = $this->_getSelected();
        $collection = $this->commResourceLocationCollectionFactory->create();
        $collection->addFieldToFilter('code', array(
            'in' => $locationIds
        ));
        $collection->getSelect()->order('sort_order ASC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Used in grid to return selected customers values.
     */
    protected function _getSelected()
    {
        $helperbranch = $this->branchPickupHelper;
        /* @var Epicor_BranchPickup_Helper_Data */
        return array_keys($helperbranch->getSelected());
    }

    /**
     * Configuration of grid
     * @return \Magento\Backend\Block\Widget\Grid
     * Build columns for List Addresses
     */
    protected function _prepareColumns()
    {



        $this->addColumn('location_name', array(
            'header' => __('Name'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('address1', array(
            'header' => __('Street'),
            'width' => '150',
            'index' => 'address1',
            'filter_index' => 'address1',
            'renderer' => 'Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer\Street',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('city', array(
            'header' => __('City'),
            'width' => '150',
            'index' => 'city',
            'filter_index' => 'city'
        ));

        $this->addColumn('county', array(
            'header' => __('Region'),
            'width' => '150',
            'index' => 'county',
            'filter_index' => 'county'
        ));


        $this->addColumn('country', array(
            'header' => __('Country'),
            'width' => '150',
            'index' => 'country',
            'type' => 'country',
            'filter_index' => 'country'
        ));

        $this->addColumn('postcode', array(
            'header' => __('Postal Code'),
            'width' => '150',
            'index' => 'postcode',
            'filter_index' => 'postcode'
        ));

        $this->addColumn('select', array(
            'header' => __('Select'),
            'width' => '280',
            'index' => 'code',
            'renderer' => 'Epicor\BranchPickup\Block\Pickup\Select\Grid\Renderer\Select',
            'links' => 'true',
            'getter' => 'getCode',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'actions' => array(
                array(
                    'caption' => __('Select'),
                    'url' => '',
                    'id' => 'link',
                    'onclick' => 'changeBranPickupLocation(this); return false;'
                )
            )
        ));


        $this->addColumn('location_code', array(
            'header' => __('Location Code'),
            'width' => '0',
            'index' => 'code',
            'filter_index' => 'code',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));


        return parent::_prepareColumns();
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/selectgrid', array(
                '_current' => true
        ));
    }

}
