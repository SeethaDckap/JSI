<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class AllowedCustomer extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    protected $_accountType = 'mage_customer';
    protected $_restrictToType = 'mage_customer';
    protected $_notAllowedCustomerTypes = [];
    protected $_defaultLabel = 'No Customer Selected';
    protected $_masterShopDisableTypes = array("salesrep", "guest", "supplier");

    protected $_types = array(
        'mage_customer' => array(
            'label' => 'Customer',
            'field' => 'id',
            'model' => 'customer/customer',
            'url' => 'adminhtml/epicorcomm_customer/allowedcustomers/',
            'priority' => 10
        )
    );
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Epicor\Common\Model\AccountTypeModelReader
     */
    protected $accountTypeModelReader;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Epicor\Common\Model\AccountTypeModelReader $accountTypeModelReader,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = [])
    {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->accountTypeModelReader = $accountTypeModelReader;
        $this->url = $url;
        $this->customerFactory = $customerFactory;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('text');
        $this->setExtType('textfield');
        $this->addClass('erpaccount-type-field');

        $this->_notAllowedCustomerTypes = $this->getData('notAllowedCustomerTypes');

    }


    private function getLabelValue($accountTypeValue, $accountTypeInfo = null)
    {
        $accountId = null;

        $label = __($this->_defaultLabel);
        if (!empty($accountTypeInfo)) {
            $accountId = $accountTypeValue;
        } else {
            $accountTypes = $this->getAccountTypes();
            $accountTypeInfo = isset($accountTypes[$accountTypeValue]) ? $accountTypes[$accountTypeValue] : '';

            if (isset($accountTypeInfo['field'])) {
                /* @var $customer Epicor_Comm_Model_Customer */
                $customer = $this->registry->registry('current_customer');
                if ($customer) {
                    $accountId = $customer->getData($accountTypeInfo['field']);
                }
            }
        }

        if ($accountId) {
            //M1 > M2 Translation Begin (Rule 46)
            //$accountModel = Mage::getModel($accountTypeInfo['model'])->load($accountId);
            $accountModel = $this->accountTypeModelReader->getModel($accountTypeInfo['model'])->load($accountId);
            //M1 > M2 Translation End
            if (!$accountModel->isObjectNew()) {
                $label = $accountModel->getName();
            }
        }

        return $label;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('input-text');
        /* @var $helper \Epicor\Common\Helper\Data */
        $helper = $this->commonHelper;
        $baseFieldName = $this->getName();
        $types = $this->getAccountTypes();
        if (empty($this->_restrictToType)) {

            $accountType = $this->getAccountType();

            //Disable Master Shoppers for  $this->_masterShopDisableTypes;
            $disableMaster = in_array($accountType, $this->_masterShopDisableTypes);
            /* @var $customer \Epicor\Comm\Model\Customer */
            $customer = $this->registry->registry('current_customer');

            $selectHtml = '<div id="ecc_account_select_type"><select name="' . $this->getName() . '" id="' . $this->getHtmlId() . '"' . $this->serialize($this->getHtmlAttributes()) .  $this->_getUiId() .'>';
            $typesHtml = '';
            foreach ($types as $value => $info) {
                $selected = $accountType == $value ? ' selected="selected"' : '';
                if($value =='customer'){
                    $selectHtml .= '<option value="' . $value . '"' . $selected . '>Erp Account</option>';
                }else{
                    $selectHtml .= '<option value="' . $value . '"' . $selected . '>' . $info['label'] . '</option>';
                }

                if (isset($info['field'])) {
                    $typesHtml .= $this->getAccountTypeHtml($baseFieldName, $value, $info, $customer);
                }
            }
            $selectHtml .= '</select></div>';
            $ms_shopper = $this->scopeConfig->getValue("customerconnect_enabled_messages/CUCO_mapping/master_shopper_default_value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $selectHtml .= '
            <script type="text/javascript">
            require(["jquery","prototype"], function(jQuery){
                Event.observe("' . $this->getHtmlId() . '", "change", function(event) {
                    el = Event.element(event);
                    accountSelector.switchType("' . $this->getHtmlId() . '", el.options[el.selectedIndex].value);
                    var accountSelect = el.options[el.selectedIndex].value;
                    if(accountSelect !="customer") { 
                       jQuery("#notapplicable").show();
                       jQuery("#ecc_master_shopper").val(0);
                       jQuery("#ecc_master_shopper").hide();                         
                    } else {
                        jQuery("#notapplicable").hide();  
                       jQuery("#ecc_master_shopper").val(' . $ms_shopper . ');
                       jQuery("#ecc_master_shopper").show();     
                    }
                });
            });
            </script>';

            //If the Account type is not customer then Load this js to hide the master shopper
            if ($disableMaster) {
                $selectHtml .= '
            <script type="text/javascript">
            //<![CDATA[
                require(["jquery"],function($){
                     $(document).ready(function(){
                       $("#ecc_master_shopper").before("<div id=\"notapplicable\">Not Applicable</div>");  
                       $("#ecc_master_shopper").val(0);
                       $("#ecc_master_shopper").hide();
                       $("#notapplicable").show();
                    });
                    
                });
            //]]>
            </script>';
            } else {
                $selectHtml .= '
            <script type="text/javascript">
            //<![CDATA[
            require(["jquery"],function($){
                    $(document).ready(function(){
                       $("#ecc_master_shopper").before("<div id=\"notapplicable\">Not Applicable</div>");  
                       $("#notapplicable").hide();
                    });
            });
            //]]>
            </script>';
            }

            $currentAccountInfo = isset($types[$accountType]) ? $types[$accountType] : '';
            $display = (isset($currentAccountInfo['field'])) ? '' : ' style="display:none"';
            $labelValue = $this->getLabelValue($accountType);
        } else {
            $display = '';
            $selectHtml = '<input type="hidden" name="' . $this->getName() . '" id="' . $this->getHtmlId() . '" value="' . $this->_restrictToType . '">';
            $typesHtml = $this->getAccountTypeHtml($baseFieldName, $this->_restrictToType, $types[$this->_restrictToType]);
            $labelValue = $this->getLabelValue($this->getValue(), $types[$this->_restrictToType]);
        }

        $html = $selectHtml . $typesHtml;

        $html .= '<input type="hidden" name="account_type_no_label" id="' . $this->getHtmlId() . '_no_account" value="' . __('No Account Selected') . '" />';
        $html .= '<div id="ecc_account_selector"' . $display . '><span id="' . $this->getHtmlId() . '_label" class="erpaccount_label">' . $labelValue . '</span>';
        $html .= '<button class="form-button" id="' . $this->getHtmlId() . '_trig" onclick="accountSelector.openpopup(\'' . $this->getHtmlId() . '\'); return false;">' . __('Select') . '</button>';
        $html .= '<button class="form-button" id="' . $this->getHtmlId() . '_remove" onclick="accountSelector.removeAccount(\'' . $this->getHtmlId() . '\'); return false;">' . __('Remove') . '</button></div>';

        $html .= $this->getAfterElementHtml();

        return $html;
    }

    protected function getAccountTypeHtml($baseFieldName, $type, $info, $customer = null)
    {
        $params =[];
        if(!empty($this->_notAllowedCustomerTypes)){
            $notallowedCustomer=implode("||",$this->_notAllowedCustomerTypes);
            $params['notAllowedCustomerTypes'] = $notallowedCustomer;
        }

        $accountId = ($customer) ? $customer->getData($info['field']) : $this->getValue();
        $fieldName = str_replace('ecc_erp_account_type', $info['field'], $baseFieldName);
        $typesHtml = '<div id="' . $type . '_acctype">';
        $typesHtml .= '<input type="hidden" name="' . $type . '_label" id="' . $this->getHtmlId() . '_' . $type . '_label" value="" />';
        $typesHtml .= '<input type="hidden" name="' . $type . '_field" id="' . $this->getHtmlId() . '_' . $type . '_field" value="' . $info['field'] . '" />';
        $typesHtml .= '<input type="hidden" name="' . $type . '_url" id="' . $this->getHtmlId() . '_' . $type . '_url" value="' . $this->url->getUrl($info['url'],$params) . '" />';
        $typesHtml .= '<input type="hidden" data-form-part="customer_form" name="' . $fieldName . '" id="' . $this->getHtmlId() . '_account_id_' . $type . '" value="' . $accountId . '" class="type_field"/>';
        $typesHtml .= '</div>';
        return $typesHtml;
    }

    protected function getAccountType()
    {
        if (empty($this->_accountType)) {
            $this->_accountType = $this->getValue();
            /* @var $customer \Epicor\Comm\Model\Customer */
            $customer = $this->getCustomer();
            if (empty($this->_accountType) && $customer) {

                $helper = $this->commonAccountSelectorHelper;
                /* @var $helper \Epicor\Common\Helper\Account\Selector */
                $sortedTypes = $helper->getAccountTypesByPriority();

                foreach ($sortedTypes as $type) {
                    if (isset($type['field'])) {
                        $accountId = $customer->getData($type['field']);
                        if (!empty($accountId)) {
                            $this->_accountType = $type['value'];
                            break;
                        }
                    } else if ($type['priority'] == 0) {
                        $this->_accountType = $type['value'];
                    }
                }
            }
        }

        return $this->_accountType;
    }

    public function getAccountTypes()
    {
        if (!$this->_types) {
            $helper = $this->commonAccountSelectorHelper;
            $types = $helper->getAccountTypes();

            if (!empty($this->_restrictToType)) {
                $this->_types = array($this->_restrictToType => $types[$this->_restrictToType]);
            } else {
                $this->_types = $types;
            }
        }

        return $this->_types;
    }


    private function getCustomer()
    {

        $customerId = $this->registry->registry('current_customer_id');
        $customer = $this->registry->registry('current_customer');
        if (!$customer) {
            $customer = $this->customerFactory->create()->load($customerId);
            $this->registry->register('current_customer', $customer);
        }

        return $customer;
    }

}

