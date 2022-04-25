<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Contracts;


/**
 * List ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * Builds ERP Accounts Contracts Form
 *
 * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Contracts\Form
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    public $_form;
    public $_formData;
    public $_account;
    public $_type;
    public $_default = array();
    public $_prefix = array();

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->registry = $registry;
        $this->listsHelper = $listsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Contracts';
        $this->_default = array('customer' => 'ERP Account Default', 'erpaccount' => 'Global Default');
        $this->_prefix = array('customer' => 'ecc_', 'erpaccount' => '');
    }

    protected function _prepareForm()
    {

        $this->_form = $this->formFactory->create();
        $this->_formData = $this->backendSession->getFormData(true);

        if (empty($this->_formData)) {
            $this->_formData = $this->_account->getData();
        }
        if ($this->registry->registry('current_customer')) {
            $fieldset = $this->_form->addFieldset('contracts_default', array('legend' => __('Default Contract Settings')));
            $eccContractsFilter = $fieldset->addField('ecc_contracts_filter', 'multiselect', array(
                'label' => __('Contract Filter'),
                'required' => false,
                'name' => 'ecc_contracts_filter',
                'values' => $this->getContractHtml(),
            ));
            $eccContractsFilter->setAfterElementHtml("
                    <script> 
                     //<![CDATA[
                         var selectedOption = $('ecc_contracts_filter');
                         Event.observe('ecc_contracts_filter', 'change', function(event) {
                         for (i = 0; i < selectedOption.options.length; i++) {
                         var currentOption = selectedOption.options[i];
                         if (currentOption.selected && currentOption.value =='') {
                            for (var i=1; i<selectedOption.options.length; i++) {
                                selectedOption.options[i].selected = false;
                            }                         
                         }
                         }
                         })
                    //]]>
                    </script>
                    ");

            $ecc_default_contract = $fieldset->addField('ecc_default_contract', 'select', array(
                'label' => __('Default Contract'),
                'required' => false,
                'name' => 'ecc_default_contract',
                'values' => $this->getContractHtml(),
            ));
            $ecc_default_contract->setAfterElementHtml("
                    <script> 
                     //<![CDATA[
                     var reloadurl = '" . $this->getUrl('adminhtml/epicorlists_list/fetchaddress/') . "';
                        Event.observe('ecc_default_contract', 'change', function(event) {
                            fetchAddressInList(reloadurl);
                        });
                       fetchAddressInList(reloadurl);
                    //]]>
                    </script>
                    ");


            $fieldset->addField('ecc_default_contract_address', 'select', array(
                'label' => __('Default Contract Address'),
                'required' => false,
                'id' => 'ecc_default_contract_address',
                'name' => 'ecc_default_contract_address',
                'values' => $this->listsHelper->customerSelectedAddressById(),
            ));
        }
        if ($this->_type == 'erpaccount') {
            $fieldset = $this->_form->addFieldset('contracts_form', array('legend' => __('Contracts')));
            $fieldset->addField('allowed_contract_type', 'select', array(
                'label' => __('Allowed Contract Type'),
                'required' => false,
                'style'   => "width:200px",
                'name' => 'allowed_contract_type',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Header Only'),
                        'value' => 'H',
                    ),
                    array(
                        'label' => __('Both Header and Line'),
                        'value' => 'B',
                    ),
                    array(
                        'label' => __('None'),
                        'value' => 'N',
                    ),
                ),
            ));
            $fieldset->addField('required_contract_type', 'select', array(
                'label' => __('Required Contract Type'),
                'required' => false,
                'style'   => "width:200px",
                'name' => 'required_contract_type',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Header'),
                        'value' => 'H',
                    ),
                    array(
                        'label' => __('Either Header or Line'),
                        'value' => 'E',
                    ),
                    array(
                        'label' => __('Optional'),
                        'value' => 'O',
                    ),
                ),
            ));
            $fieldset->addField('allow_non_contract_items', 'select', array(
                'label' => __('Allow Non Contract Items'),
                'required' => false,
                'style'   => "width:200px",
                'name' => 'allow_non_contract_items',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }

        if ($this->scopeConfig->getValue('epicor_lists/contracts/shipto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset($this->_prefix[$this->_type] . 'contracts_shipto_form', array('legend' => __('Ship To Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/shiptoselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_default', 'select', array(
                    'label' => __('Contracts Ship To Default'),
                    'required' => false,
                    'style'   => "width:200px",
                    'name' => $this->_prefix[$this->_type] . 'contract_shipto_default',
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Shoppers Default Ship To'),
                            'value' => 'default',
                        ),
                        array(
                            'label' => __('Specific Ship To'),
                            'value' => 'specific',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }
            if ($this->scopeConfig->getValue('epicor_lists/contracts/shiptodate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_date', 'select', array(
                    'label' => __('Contract Ship To Date'),
                    'required' => false,
                    'style'   => "width:200px",
                    'name' => $this->_prefix[$this->_type] . 'contract_shipto_date',
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Newest Activation Date'),
                            'value' => 'newest',
                        ),
                        array(
                            'label' => __('Oldest Activation Date'),
                            'value' => 'oldest',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_shipto_prompt', 'select', array(
                'label' => __('Contract Ship To Prompt'),
                'required' => false,
                'style'   => "width:200px",
                'name' => $this->_prefix[$this->_type] . 'contract_shipto_prompt',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }

        if ($this->scopeConfig->getValue('epicor_lists/contracts/header', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset('contracts_header', array('legend' => __('Header Contract Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/headerselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_selection', 'select', array(
                    'label' => __('Contract Header Selection'),
                    'required' => false,
                    'style'   => "width:200px",
                    'name' => $this->_prefix[$this->_type] . 'contract_header_selection',
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Newest'),
                            'value' => 'newest',
                        ),
                        array(
                            'label' => __('Oldest'),
                            'value' => 'oldest',
                        ),
                        array(
                            'label' => __('Most Recently Used'),
                            'value' => 'recent',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }


            $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_prompt', 'select', array(
                'label' => __('Contract Header Prompt'),
                'required' => false,
                'style'   => "width:200px",
                'name' => $this->_prefix[$this->_type] . 'contract_header_prompt',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_header_always', 'select', array(
                'label' => __('Contract Header Always'),
                'required' => false,
                'style'   => "width:200px",
                'name' => $this->_prefix[$this->_type] . 'contract_header_always',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }


        if ($this->scopeConfig->getValue('epicor_lists/contracts/line', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $fieldset = $this->_form->addFieldset('contracts_line', array('legend' => __('Line Contract Selection Settings')));
            if ($this->scopeConfig->getValue('epicor_lists/contracts/lineselection', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all') {
                $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_selection', 'select', array(
                    'label' => __('Contract Line Selection'),
                    'required' => false,
                    'style'   => "width:200px",
                    'name' => $this->_prefix[$this->_type] . 'contract_line_selection',
                    'values' => array(
                        array(
                            'label' => __('All'),
                            'value' => 'all',
                        ),
                        array(
                            'label' => __('Lowest'),
                            'value' => 'lowest',
                        ),
                        array(
                            'label' => __('Highest'),
                            'value' => 'highest',
                        ),
                        array(
                            'label' => __($this->_default[$this->_type]),
                            'value' => '',
                        ),
                    ),
                ));
            }


            $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_prompt', 'select', array(
                'label' => __('Contract Line Prompt'),
                'required' => false,
                'style'   => "width:200px",
                'name' => $this->_prefix[$this->_type] . 'contract_line_prompt',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));

            $fieldset->addField($this->_prefix[$this->_type] . 'contract_line_always', 'select', array(
                'label' => __('Contract Line Always'),
                'required' => false,
                'style'   => "width:200px",
                'name' => $this->_prefix[$this->_type] . 'contract_line_always',
                'values' => array(
                    array(
                        'label' => __($this->_default[$this->_type]),
                        'value' => '',
                    ),
                    array(
                        'label' => __('Yes'),
                        'value' => '1',
                    ),
                    array(
                        'label' => __('No'),
                        'value' => '0',
                    ),
                ),
            ));
        }


        $this->_form->setValues($this->_formData);
        $this->setForm($this->_form);

        return parent::_prepareForm();
    }
    /**
     * Get customer address
     *
     * @param $addressId
     * @param $customerId
     * @return string $options
     */
    function customerSelectedAddressById($customer)
    {
        $options = [];
        if ($customerId = $customer->getId()) {
            $loadHelper = $this->commonHelper->customerListAddressesById($addressId, $customerId);
            $customerData = $customer;
            $defaultContractAddress = $customerData->getEccDefaultContractAddress();
            $options[] = ['label' => 'No Default Address', 'value' => ''];
            if ($loadHelper) {
                foreach ($loadHelper as $code => $address) {
                    $options[] = ['label' =>  $address->getName() , 'value' => $code];
                }
            }
        }
        return $options;
    }

    /**
     * Get customer contract 
     * @return array
     */
    public function getContractHtml()
    {
        /* @var $customer Epicor_Comm_Model_Customer */
        $customer = $this->registry->registry('current_customer');
        $contracts = $this->commonHelper->customerListsById($customer->getId(), 'filterContracts');
        $contractFilter = $customer->getEccContractsFilter();
        $messages[] = ['label' => 'No Default Contract', 'value' => ''];
        foreach ($contracts['items'] as $info) {
            $code = $info['id'];
            $messages[] = [
                'label' => $info['title'],
                'value' => $code,
            ];
        }
        return $messages;
    }

}
