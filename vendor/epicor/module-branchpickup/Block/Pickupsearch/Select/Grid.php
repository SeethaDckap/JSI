<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickupsearch\Select;


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

    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';
    
    
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;    
    

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer\StreetFactory $branchPickupPickupsearchSelectRendererStreetFactory,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        array $data = []
    )
    {
        $this->branchPickupPickupsearchSelectRendererStreetFactory = $branchPickupPickupsearchSelectRendererStreetFactory;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->messageManager = $messageManager;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->configProvider = $configProvider;
        $this->responseFactory = $responseFactory;
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

    
    
    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '<script>
                window.checkoutConfig = '. \Zend_Json::encode($this->getCheckoutConfig()).';
                // Create aliases for customer.js model from customer module
                window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;
                window.customerData = window.checkoutConfig.customerData;
                function populateBranchAddressSelect(locationId,locationCode,errorCode){
                    require(["jquery","Magento_Checkout/js/model/full-screen-loader","Magento_Checkout/js/checkout-data","Epicor_BranchPickup/js/epicor/view/popupsearch","jquery/ui"], function($,fullScreenLoader,checkoutData,popupsearch){
                                    fullScreenLoader.startLoader();
                                    if(errorCode !="2") {
                                      var vals =  popupsearch.checkItemsExists(locationCode,locationId,errorCode);
                                    } else {
                                        fullScreenLoader.startLoader();
                                        $("#jsonBranchfiltervals", window.parent.document).val(locationCode);
                                        $("#jsonBranchfilteraddress", window.parent.document).val(locationId);
                                        $("#jsonBranchfiltererror", window.parent.document).val(errorCode);
                                        $( ".action-close", window.parent.document).trigger( "click" );
                                        $("#selectSearchBranchAddress_"+locationId, window.parent.document).trigger("click");                                    
                                        fullScreenLoader.stopLoader();                                    
                                    }
                    });                    
                }
            </script>';
        return $html;
    }      

    
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
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

        $this->addColumn('street', array(
            'header' => __('Street'),
            'width' => '150',
            'renderer' => 'Epicor\BranchPickup\Block\Pickupsearch\Select\Renderer\Street',
            'filter_condition_callback' => array($this, '_streetFilter'),
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
            'renderer' => 'Epicor\BranchPickup\Block\Pickupsearch\Select\Grid\Renderer\Select',
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
        return $this->getUrl('*/*/pickupsearchgrid', array(
                '_current' => true
        ));
    }
    
    /**
     * enable search for street column(WSO-4177)
     */
    protected function _streetFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()->where(
            "address1 like ?
            OR address2 like ?
            OR address3 like ?"
        , "%$value%");
        return $this;
    }    

}