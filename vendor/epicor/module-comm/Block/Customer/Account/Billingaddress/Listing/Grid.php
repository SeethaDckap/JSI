<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Account\Billingaddress\Listing;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Address\Renderer\AddressFactory
     */
    protected $commCustomerAddressRendererAddressFactory;

    /**
     * @var \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory
     */
    protected $commCustomerAddressRendererStreetFactory;
    
    
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;        

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,    
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper, 
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Epicor\Comm\Block\Customer\Address\Renderer\AddressFactory $commCustomerAddressRendererAddressFactory,
        \Epicor\Comm\Block\Customer\Address\Renderer\StreetFactory $commCustomerAddressRendererStreetFactory,
         array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commCustomerAddressRendererAddressFactory = $commCustomerAddressRendererAddressFactory;
        $this->commCustomerAddressRendererStreetFactory = $commCustomerAddressRendererStreetFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_checkoutSession = $checkoutSession;
        $this->configProvider = $configProvider;
        $this->eventManager = $context->getEventManager();
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

//        $his->setRowClickCallback('editBillingAddress');

        $this->setId('customer_account_billingaddress_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setRowClickCallback('populateAddressSelect');
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
//        /* @var $order Epicor_Common_Model_Xmlvarien */
//        
        $addresses = array();
        $loadAddresses = true;
        $restrict = $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setAddresses($addresses);
        $transportObject->setLoadAddresses($loadAddresses);
        $transportObject->setRestrictByType($restrict);

        $quote =  $this->_checkoutSession->getQuoteOnly();

        /* @var $cart Mage_Checkout_Model_Cart */

        $this->eventManager->dispatch('epicor_comm_onepage_get_checkout_addresses', array('quote' => $quote, 'type' => 'invoice', 'addresses' => $transportObject));
        $addresses = $transportObject->getAddresses();
        $loadAddresses = $transportObject->getLoadAddresses();

        if ($loadAddresses) {
            $customer = $this->customerSessionFactory->create()->getCustomer();
            $addresses = ($restrict) ? $customer->getAddressesByType('invoice') : $customer->getCustomAddresses();
        }

        $this->setCustomData($addresses);
    }

    
    public function _toHtml()
    {
        $html = parent::_toHtml();


        $html .= '<script>
                window.checkoutConfig = '. \Zend_Json::encode($this->getCheckoutConfig()).';
                // Create aliases for customer.js model from customer module
                window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;
                window.customerData = window.checkoutConfig.customerData;
                function populateAddressSelect(row, event){
                    event.preventDefault();
                    var trElement = event.findElement("tr");
                    var objectFromJson = trElement.select("input[name=details]")[0].value;
                    var arrayFromJson = objectFromJson.evalJSON();
                    require(["jquery",
                            "Magento_Checkout/js/action/select-billing-address","Magento_Checkout/js/checkout-data","Magento_Checkout/js/model/full-screen-loader","jquery/ui"], function($,
                            selectBillingAddress,checkoutData,fullScreenLoader){
                                fullScreenLoader.startLoader();
                                selectBillingAddress(objectFromJson);
                                if(arrayFromJson["entity_id"] ==null) {
                                  checkoutData.setSelectedBillingAddress("customer-address"+arrayFromJson["erp_address_id"]);   
                                  $("#jsonBillingfiltervals", window.parent.document).val("customer-address"+arrayFromJson["erp_address_id"]);
                                } else {
                                  checkoutData.setSelectedBillingAddress("customer-address"+arrayFromJson["entity_id"]);   
                                  $("#jsonBillingfiltervals", window.parent.document).val("customer-address"+arrayFromJson["entity_id"]);
                                }
                                $("#jsonBillingfilteraddress", window.parent.document).val(objectFromJson);
                                setTimeout(function(){ 
                                $( ".action-close", window.parent.document).trigger( "click" );
                                $("#selectSearchBillingAddress", window.parent.document).trigger("click");                                    
                                },1000);

                    });                    
                }
            </script>';
        return $html;
    }       

    protected function _getColumns()
    {
        $columns = array(
            'entity_id' => array(
                'header' => __('id'),
                'align' => 'left',
                'index' => 'entity_id',
                'renderer' => '\Epicor\Comm\Block\Customer\Account\Billingaddress\Renderer\Billingaddress',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'company' => array(
                'header' => __('Company'),
                'align' => 'left',
                'index' => 'company',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'street' => array(
                'header' => __('Street'),
                'align' => 'left',
                'index' => 'street',
                'renderer' => 'Epicor\Comm\Block\Customer\Address\Renderer\Street',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'city' => array(
                'header' => __('City'),
                'align' => 'left',
                'index' => 'city',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'region' => array(
                'header' => __('Region'),
                'align' => 'left',
                'index' => 'region',
                'condition' => 'LIKE'
            ),
            'postcode' => array(
                'header' => __('Postcode'),
                'align' => 'left',
                'index' => 'postcode',
                'type' => 'postcode',
                'condition' => 'LIKE'
            ),
            'country_id' => array(
                'header' => __('Country'),
                'align' => 'left',
                'index' => 'country_id',
                'type' => 'country',
                'condition' => 'LIKE'
            ),
        );

        return $columns;
    }
    
    
    public function getCheckoutConfig()
    {
        return ['quoteData'=>$this->getQuoteData()];
    }        

    public function getRowUrl($row)
    {
        return '#';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/billingPopupGrid', array(
                '_current' => true
        ));  
    }

}