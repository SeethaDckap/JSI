<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class Quickstart extends \Magento\Framework\App\Helper\AbstractHelper
{

    const EPICOR_COMM_LICENSING_ERP_KEY = 'Epicor_Comm_licensing_erp';
    const EPICOR_COMM_LICENSING_ERP_VALUE = 'Epicor_Comm[licensing][erp]';
    const ORDER_PREFIX_CONFIG_PATH = 'epicor_comm_enabled_messages/gor_request/gor_order_prefix';
    const QUOTE_PREFIX_CONFIG_PATH = 'epicor_quotes/general/prefix';
    const RETURNS_PREFIX_CONFIG_PATH = 'epicor_comm_returns/returns/prefix';
    const AR_PAYMENT_PREFIX_CONFIG_PATH = 'customerconnect_enabled_messages/CAAP_request/prefix';
    const CUSTOMER_QS_DEFAULT_ERP_ACCOUNT = 'customer/create_account/qs_default_erpaccount';

    static $CONFIG_FIELDS = array(
        'erp' => array(
            'title' => 'ERP Settings',
            'fields' => array(
                'Epicor_Comm/licensing/type' => array(
                    'type' => 'hidden',
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_type' => 'Epicor_Comm[licensing][type]'
                    ),
                ),
                'Epicor_Comm/licensing/erp' => array(
                    'dependency_mapping' => array(
                        self::EPICOR_COMM_LICENSING_ERP_KEY => self::EPICOR_COMM_LICENSING_ERP_VALUE
                    )
                )
            ),
        ),
        'networking' => array(
            'title' => 'Networking Settings',
            'fields' => array(
                'Epicor_Comm/xmlMessaging/source',
                'Epicor_Comm/xmlMessaging/url',
                'Epicor_Comm/licensing/username' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_username' => 'Epicor_Comm[licensing][username]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][username]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('e10', 'p21')
                        )
                    ),
                    'ignore_for_erps' => array('xvp', 'tropos','eclipse','bistrack'),
                ),
                'Epicor_Comm/licensing/password' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_password' => 'Epicor_Comm[licensing][password]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][password]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
//                            'value' => 'e10',
                            'value' => array('e10', 'p21')
                        )
                    ),
                    'ignore_for_erps' => array('xvp', 'tropos','eclipse','bistrack'),
                ),
                'Epicor_Comm/licensing/p21_token_url' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_p21_token_url' => 'Epicor_Comm[licensing][p21_token_url]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][p21_token_url]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('p21')
                        )
                    ),
                    'ignore_for_erps' => array('e10', 'xvp', 'tropos','eclipse','bistrack'),
                ),
                'Epicor_Comm/licensing/p21_token_get' => array(
                    'type' => 'button',
                    'label' => '',
                    'value' => 'Get P21 Token',
                    'on_click' => 'quickstart.getP21Token(\'Epicor_Comm/licensing/p21_token_url\',\'Epicor_Comm/licensing/username\',\'Epicor_Comm/licensing/password\', this)',
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_p21_token_get' => 'Epicor_Comm[licensing][p21_token_get]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][p21_token_get]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('p21')
                        )
                    ),
                    'ignore_for_erps' => array('e10', 'xvp', 'tropos','eclipse','bistrack'),
                ),
                'Epicor_Comm/licensing/company' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_company' => 'Epicor_Comm[licensing][company]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][company]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('e10', 'p21')
                        )
                    ),
                    'ignore_for_erps' => array('xvp', 'tropos','eclipse','bistrack'),
                ),
                'button/test_connection' => array(
                    'type' => 'button',
                    'label' => '',
                    'value' => 'Test Network Connection',
                    'on_click' => 'quickstart.testConnection(\'Epicor_Comm/xmlMessaging/url\',\'Epicor_Comm/licensing/username\',\'Epicor_Comm/licensing/password\', this)'
                ),
            )
        ),
        'licensing' => array(
            'title' => 'Licensing Settings',
            'fields' => array(
                'Epicor_Comm/licensing/cert_file' => array(
                    'type' => 'adminfile',
                    'field_name' => 'Epicor_Comm[licensing][cert_file]'
                ),
                'button/request_license' => array(
                    'type' => 'button',
                    'label' => '',
                    'value' => 'Request License from ERP',
                    'on_click' => 'quickstart.requestLicense(\'Epicor_Comm/xmlMessaging/url\',\'Epicor_Comm/licensing/username\',\'Epicor_Comm/licensing/password\', this)',
                    'dependency_mapping' => array(
                        'button_request_license' => 'button[request_license]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'button[request_license]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('e10')
                        )
                    ),
                    'ignore_for_erps' => array('xvp', 'tropos', 'p21')
                ),
                'Epicor_Common/adminhtml_licfor' => array(
                    'type' => 'note',
                    'field_name' => 'Epicor_Common[adminhtml_licfor]',
                    'label' => 'Current License Accessibility',
                ),
            )
        ),
        'site_monitoring' => array(
            'title' => 'Site Monitoring',
            'fields' => array(
                'Epicor_Comm/site_monitoring/code_snippet' => array(
                    'type' => 'text',
                    'label' => 'Code Snippet',
                    'optional' => true,
                    'comment' => 'Example &lt;script src = "example.js"&gt;&lt;/script&gt;'
                )
            )
        ),
        'customer' => array(
            'license' => array('Consumer', 'Customer'),
            'title' => 'Customer Settings',
            'fields' => array(
                'customer/create_account/qs_default_erpaccount' => array(
                    'type' => 'text',
                    'label' => 'Default ERP Code',
                    'optional' => false,
                ),
                'customer/create_account/default_erpaccount' => array(
                    'label' => '',
                    'type' => 'account_selector'
                ),
                'general/country/default',
                'mapping/customer_tax_classes' => array(
                    'label' => 'Customer Tax Classes',
                    'type' => 'mapping',
                    'optional' => false,
                    'source_model' => 'tax/class',
                    'fields_to_filter' => array(
                        'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
                    ),
                    'mapping_fields' => array(
                        'class_name' => array(
                            'type' => 'text',
                            'label' => 'Customer Tax Class',
                            'class' => 'customer_tax'
                        )
                    ),
                    'delete_callback' => 'quickstart.removeTaxClassRow'
                ),
                'epicor_comm_field_mapping/cus_mapping/customer_use_multiple_customer_groups' => array(
                    'optional' => true,
                    'dependency_mapping' => array(
                        'epicor_comm_field_mapping_cus_mapping_customer_use_multiple_customer_groups' => 'epicor_comm_field_mapping[cus_mapping][customer_use_multiple_customer_groups]'
                    ),
                ),
                'epicor_comm_field_mapping/cus_mapping/customer_default_customer_group' => array(
                    'optional' => true,
                    'dependency_mapping' => array(
                        'epicor_comm_field_mapping_cus_mapping_customer_default_customer_group' => 'epicor_comm_field_mapping[cus_mapping][customer_default_customer_group]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_field_mapping[cus_mapping][customer_default_customer_group]',
                            'depends_on' => 'epicor_comm_field_mapping[cus_mapping][customer_use_multiple_customer_groups]',
                            'value' => '0',
                        )
                    ),
                ),
                'epicor_comm_field_mapping/cus_mapping/customer_taxcode_default',
                'mapping/country_mapping' => array(
                    'label' => 'Country Mapping',
                    'optional' => false,
                    'type' => 'mapping',
                    'source_model' => 'epicor_comm/erp_mapping_country',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'erp_code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'magento_id' => array(
                            'type' => 'select',
                            'label' => 'Magento Country',
                            'class' => 'required-entry',
                            'source_model' => 'adminhtml/system_config_source_country',
                        )
                    )
                ),
                'mapping/currency_mapping' => array(
                    'label' => 'Currency Mapping',
                    'optional' => false,
                    'type' => 'mapping',
                    'source_model' => 'epicor_comm/erp_mapping_currency',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'erp_code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'magento_id' => array(
                            'type' => 'select',
                            'label' => 'Country',
                            'source_model' => 'adminhtml/system_config_source_currency'
                        )
                    )
                ),
            )
        ),
        'products' => array(
            'license' => array('Consumer', 'Customer'),
            'title' => 'Product Settings',
            'fields' => array(
                'epicor_comm_field_mapping/stk_mapping/unit_of_measure_filter' => array(
                    'optional' => true
                ),
                'mapping/product_tax_classes' => array(
                    'label' => 'Product Tax Classes',
                    'type' => 'mapping',
                    'source_model' => 'tax/class',
                    'fields_to_filter' => array(
                        'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
                    ),
                    'mapping_fields' => array(
                        'class_name' => array(
                            'type' => 'text',
                            'label' => 'Product Tax Class'
                        )
                    ),
                    'delete_callback' => 'quickstart.removeTaxClassRow'
                ),
                'epicor_comm_field_mapping/stk_mapping/tax_code_default',
                'mapping/language_mapping' => array(
                    'label' => 'Language Mapping',
                    'type' => 'mapping',
                    'source_model' => 'epicor_common/erp_mapping_language',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'erp_code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'language_codes' => array(
                            'type' => 'multiselect',
                            'label' => 'Language',
                            'source_model' => 'adminhtml/system_config_source_locale'
                        )
                    )
                ),
            )
        ),
        'Configurator' => array(
            'license' => array('Consumer_Configurator', 'Customer_Configurator'),
            'title' => 'Configurator Settings',
            'fields' => array(
                'Epicor_Comm/licensing/ewa_username' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_ewa_username' => 'Epicor_Comm[licensing][ewa_username]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][ewa_username]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'Epicor_Comm/licensing/ewa_password' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_ewa_password' => 'Epicor_Comm[licensing][ewa_password]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][ewa_password]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'epicor_comm_enabled_messages/cim_request/ewa_url' => array(
                    'dependency_mapping' => array(
                        'epicor_comm_enabled_messages_cim_request_ewa_url' => 'epicor_comm_enabled_messages[cim_request][ewa_url]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_enabled_messages[cim_request][ewa_url]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'epicor_comm_enabled_messages/cim_request/ewa_css' => array(
                    'dependency_mapping' => array(
                        'epicor_comm_enabled_messages_cim_request_ewa_css' => 'epicor_comm_enabled_messages[cim_request][ewa_css]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_enabled_messages[cim_request][ewa_css]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                    'optional' => true,
                ),
            )
        ),
        'ewc' => array(
            'license' => array('Consumer_Configurator', 'Customer_Configurator'),
            'title' => ' EWC Settings',
            'fields' => array(
                'Epicor_Comm/licensing/ewc_username' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_ewc_username' => 'Epicor_Comm[licensing][ewc_username]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][ewc_username]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'Epicor_Comm/licensing/ewc_password' => array(
                    'dependency_mapping' => array(
                        'Epicor_Comm_licensing_ewc_password' => 'Epicor_Comm[licensing][ewc_password]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'Epicor_Comm[licensing][ewc_password]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'epicor_comm_enabled_messages/cim_request/ewc_url' => array(
                    'dependency_mapping' => array(
                        'epicor_comm_enabled_messages_cim_request_ewc_url' => 'epicor_comm_enabled_messages[cim_request][ewc_url]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_enabled_messages[cim_request][ewc_url]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'epicor_comm_enabled_messages/cim_request/ewc_appurl' => array(
                    'dependency_mapping' => array(
                        'epicor_comm_enabled_messages_cim_request_ewc_appurl' => 'epicor_comm_enabled_messages[cim_request][ewc_appurl]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_enabled_messages[cim_request][ewc_appurl]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                ),
                'epicor_comm_enabled_messages/cim_request/ewc_css' => array(
                    'dependency_mapping' => array(
                        'epicor_comm_enabled_messages_cim_request_ewc_css' => 'epicor_comm_enabled_messages[cim_request][ewc_css]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'epicor_comm_enabled_messages[cim_request][ewc_css]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => 'e10',
                        )
                    ),
                    'optional' => true,
                ),
            )
        ),
        'checkout' => array(
            'license' => array('Consumer', 'Customer'),
            'title' => 'Checkout Settings',
            'fields' => array(
                self::ORDER_PREFIX_CONFIG_PATH,
                self::QUOTE_PREFIX_CONFIG_PATH,
                self::RETURNS_PREFIX_CONFIG_PATH,
                self::AR_PAYMENT_PREFIX_CONFIG_PATH,
                'mapping/shipping_mapping' => array(
                    'label' => 'Shipping Method Mapping',
                    'optional' => false,
                    'type' => 'mapping',
                    'source_model' => 'epicor_comm/erp_mapping_shippingmethod',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'erp_code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'shipping_method_code' => array(
                            'type' => 'select',
                            'label' => 'Shipping Method',
                            'source_model' => 'epicor_comm/erp_mapping_shipping'
                        )
                    )
                ),
                'mapping/order_status_mapping' => array(
                    'label' => 'Order Status Mapping',
                    'type' => 'mapping',
                    'source_model' => 'epicor_comm/erp_mapping_orderstatus',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'code' => array(
                            'type' => 'text',
                            'label' => 'Order Status Code'
                        ),
                        'status' => array(
                            'type' => 'select',
                            'label' => 'Order Status',
                            'source_model' => 'epicor_comm/erp_mapping_statuses'
                        ),
                        'state' => array(
                            'type' => 'select',
                            'label' => 'Order State',
                            'source_model' => 'epicor_comm/erp_mapping_states'
                        ),
                        'sou_trigger' => array(
                            'type' => 'select',
                            'label' => 'SOU Trigger',
                            'source_model' => 'epicor_comm/config_source_soutrigger'
                        )
                    )
                ),
                'mapping/payment_mapping' => array(
                    'label' => 'Payment Mapping',
                    'type' => 'mapping',
                    'source_model' => 'epicor_comm/erp_mapping_payment',
                    'fields_to_filter' => array(),
                    'optional' => false,
                    'mapping_fields' => array(
                        'magento_code' => array(
                            'type' => 'select',
                            'label' => 'Payment Method',
                            'source_model' => 'epicor_comm/erp_mapping_paymentmethods'
                        ),
                        'erp_code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'payment_collected' => array(
                            'type' => 'select',
                            'label' => 'Payment Collect',
                            'class' => 'required-entry',
                            'source_model' => 'epicor_comm/erp_mapping_payment'
                        ),
                        'gor_trigger' => array(
                            'type' => 'select',
                            'label' => 'Order Trigger',
                            'source_model' => 'epicor_comm/erp_mapping_gortriggers'
                        ),
                    )
                ),
            )
        ),
        'b2b' => array(
            'license' => array('Supplier', 'Customer'),
            'title' => 'Customer/Supplier Grid & Search Settings',
            'fields' => array(
                'mapping/invoice_mapping' => array(
                    'label' => 'Invoice Status Mapping',
                    'optional' => true,
                    'type' => 'mapping',
                    'source_model' => 'customerconnect/erp_mapping_invoicestatus',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'status' => array(
                            'type' => 'text',
                            'label' => 'Invoice Status'
                        ),
                    )
                ),
                'mapping/rma_mapping' => array(
                    'label' => 'RMA Status Mapping',
                    'optional' => true,
                    'type' => 'mapping',
                    'source_model' => 'customerconnect/erp_mapping_rmastatus',
                    'fields_to_filter' => array(),
                    'dependency_mapping' => array(
                        'mapping_rma_mapping_holder' => 'mapping[rma_mapping][origData]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'mapping[rma_mapping][origData]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('e10', 'p21'),
                        )
                    ),
                    'mapping_fields' => array(
                        'code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'status' => array(
                            'type' => 'text',
                            'label' => 'RMA Status'
                        ),
                    )
                ),
                'mapping/service_calls_mapping' => array(
                    'label' => 'Service Calls Mapping',
                    'optional' => true,
                    'type' => 'mapping',
                    'source_model' => 'customerconnect/erp_mapping_servicecallstatus',
                    'fields_to_filter' => array(),
                    'dependency_mapping' => array(
                        'mapping_service_calls_mapping_holder' => 'mapping[service_calls]'
                    ),
                    'dependencies' => array(
                        array(
                            'field' => 'mapping[service_calls]',
                            'depends_on' => 'Epicor_Comm[licensing][erp]',
                            'value' => array('e10', 'p21'),
                        )
                    ),
                    'mapping_fields' => array(
                        'code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'status' => array(
                            'type' => 'text',
                            'label' => 'Service Call Status'
                        ),
                    )
                ),
                'mapping/erp_order_status_mapping' => array(
                    'label' => 'Order Status Mapping',
                    'optional' => true,
                    'type' => 'mapping',
                    'source_model' => 'customerconnect/erp_mapping_erporderstatus',
                    'fields_to_filter' => array(),
                    'mapping_fields' => array(
                        'code' => array(
                            'type' => 'text',
                            'label' => 'ERP Code'
                        ),
                        'status' => array(
                            'type' => 'text',
                            'label' => 'Order Status'
                        ),
                    )
                ),
//                'mapping/reason_code_mapping' => array(
//                    'label' => 'Reason Code Mapping',
//                    'type' => 'mapping',
//                    'source_model' => 'customerconnect/erp_mapping_reasoncode',
//                    'fields_to_filter' => array(),
//                    'mapping_fields' => array(
//                        'code' => array(
//                            'type' => 'text',
//                            'label' => 'Reason Code'
//                        ),
//                        'description' => array(
//                            'type' => 'text',
//                            'label' => 'Reason Code Description',
//                        ),
//                        'type' => array(
//                            'type' => 'select',
//                            'label' => 'Reason Code Type',
//                            'source_model' => 'customerconnect/erp_mapping_reasoncodetypes',
//                        )
//                    )
//                ),
            ),
        )
    );

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $configConfigStructure;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var Quickstart\SourceModelReader
     */
    protected $sourceModelReader;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Config\Model\Config\Structure $configConfigStructure,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        \Epicor\Common\Helper\Quickstart\SourceModelReader $sourceModelReader
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonHelper = $commonHelper;
        $this->configConfigStructure = $configConfigStructure;
        $this->scopeConfig = $context->getScopeConfig();
        $this->fieldFactory = $fieldFactory;
        $this->sourceModelReader = $sourceModelReader;
        parent::__construct($context);
    }
    /**
     * Returns the generated quick start according to keys to render
     * @param \Magento\Framework\Data\Form $form
     * @param $keys
     * @param $sender
     * @return \Magento\Framework\Data\Form
     */
    public function _buildForm(\Magento\Framework\Data\Form $form, $keys, $sender)
    {
        $helper = $this->commonHelper;

        $form_data = array();
        $dependencies = array();
        $dependency_mapping = array();

        foreach (\Epicor\Common\Helper\Quickstart::$CONFIG_FIELDS as $id => $fieldset_data) {
            if (!in_array($id, $keys))
                continue;
            $fieldset_data = $this->dataObjectFactory->create(['data' => $fieldset_data]);
            if ($fieldset_data->getLicense()) {
                if (!$helper->isLicensedFor($fieldset_data->getLicense())) {
                    continue;
                }
            }

            $fieldset = $form->addFieldset($id, array('legend' => $fieldset_data->getTitle()));
            $fieldset->addType('account_selector', 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpaccount');
            $fieldset->addType('mapping', 'Epicor\Common\Lib\Varien\Data\Form\Element\Mapping');
            $fieldset->addType('adminfile', 'Magento\Config\Block\System\Config\Form\Field\File');

            $complete = true;
            foreach ($fieldset_data->getFields() as $path => $info) {
                if (is_numeric($path))
                    $path = $info;

                if (is_array($info) && isset($info['depends'])) {
                    if (!$this->checkDependency($info['depends'])) {
                        // dependency value not met, so skip display of this value
                        continue;
                    }
                }

                $path_bits = explode('/', $path);
                $conf = $this->configConfigStructure;

                //M1 > M2 Translation Begin (Rule 1)
                /*$section = $conf->getSection($path_bits[0]);
                $groups = array();

                if ($section) {
                    $section = $section->asCanonicalArray();
                    $groups = $section['groups'];
                }*/
                $section = $conf->getElement($path_bits[0]);
                $groups = array();
                if ($section) {
                    $groups = $section->getAttribute('children');
                }
                //M1 > M2 Translation End

                if (isset($groups[$path_bits[1]])) {
                    $field_data_array = $groups[$path_bits[1]];
                    if (isset($field_data_array['children'])) {
                        $field_data_array = $field_data_array['children'];
                        if (isset($field_data_array[$path_bits[2]])) {
                            $field_data_array = $field_data_array[$path_bits[2]];
                        } else
                            $field_data_array = array();
                    } else
                        $field_data_array = array();
                } else
                    $field_data_array = array();

                $field_data = $this->dataObjectFactory->create(['data' => $field_data_array]);
                if (is_array($info))
                    foreach ($info as $key => $value) {
                        $field_data->setData($key, $value);
                    }

                $path_code = str_replace('/', '_', $path);
                $field_name = $path_bits[0] . '[' . $path_bits[1] . ']';
                if (isset($path_bits[2]))
                    $field_name .= '[' . $path_bits[2] . ']';

                if ($field_data->getFieldName()) {
                    $field_name = $field_data->getFieldName();
                }

                if (in_array($field_data->getType(), array('hidden', 'text', 'textarea', 'obscure', 'select', 'multiselect', 'mapping', 'file', 'image', 'submit', 'button', 'account_selector', 'adminfile'))) {
                    $values = null;
                    $has_data = null;
                    switch ($field_data->getType()) {
                        case 'select':
                        case 'multiselect':
                            $values = $this->sourceModelReader->getModel($field_data->getSourceModel())->toOptionArray();
                            $form_value = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            break;

                        case 'mapping':
                            $collection = $this->sourceModelReader->getModel($field_data->getSourceModel())->getCollection();
                            /* @var $collection \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection */
                            foreach ((array) $field_data->getFieldsToFilter() as $field => $value) {
                                $collection->addFieldToFilter($field, $value);
                            }
                            $items = [];
                            foreach ($collection->getItems() as $item){
                                $items[] = $item->getData();
                            }
                            $form_value = base64_encode(serialize($items));
                            $values = $collection->getItems();
                            $has_data = $collection->count() > 0;
                            break;
                        case 'account_selector':
                            $form_value = $this->scopeConfig->getValue($path);
                            break;
                        case 'button':
                        case 'submit':
                            $form_value = $field_data->getValue();
                            break;
                        default:
                            $values = null;
                            if($path == self::QUOTE_PREFIX_CONFIG_PATH || $path == self::QUOTE_PREFIX_CONFIG_PATH || $path == self::RETURNS_PREFIX_CONFIG_PATH || $path == self::AR_PAYMENT_PREFIX_CONFIG_PATH || $path == self:: CUSTOMER_QS_DEFAULT_ERP_ACCOUNT){
                                $form_value = $this->scopeConfig->getValue($path);
                            } else {
                                $form_value = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            }
                            break;
                    }

                    if (is_null($has_data))
                        $has_data = !empty($form_value);
                    $class = $field_data->getClass();

//                    if($field_data->getFrontendType() == 'mapping'){
//                        $required = true;
//                    }else{
//                        $required = false;
//                    }
                    $fieldset->addField($path_code, $field_data->getType(), array(
                        'name' => $field_name,
                        'label' => $field_data->getLabel(),
                        'class' => $field_data->getClass(),
                        'style' => $field_data->getStyle(),
                        'values' => $values,
                        'value' => $field_data->getValue(),
                        'onclick' => $field_data->getOnClick(),
                        'note' => $field_data->getComment(),
                        'mapping_fields' => $field_data->getMappingFields(),
                        'delete_callback' => $field_data->getDeleteCallback(),
                    ));

                    if (is_array($info) && isset($info['dependencies'])) {
                        $dependencies = array_merge($dependencies, $info['dependencies']);
                    }

                    if (is_array($info) && isset($info['dependency_mapping'])) {
                        $dependency_mapping = array_merge($dependency_mapping, $info['dependency_mapping']);
                    }

                    if (
                        !$has_data &&
                        $field_data->getOptional() !== true &&
                        !in_array($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), (array) $field_data->getIgnoreForErps())
                    ) {
                        $complete = false;
                    }
                    $form_data[$path_code] = $form_value;
                }
            }
            if ($field_data->getType() == 'note') {           // if html is to be displayed direct
                if ($path == 'Epicor_Common/adminhtml_licfor') {      // retrieve current licenced for data
                    $fieldset->addField('note', 'note', array(
                        'name' => $field_name,
                        'label' => $field_data->getLabel(),
                        'text' => $this->commonHelper->getCurrentlyLicensedFor(),
                    ));
                }
            }
            if ($complete)
                $fieldset->addClass('fieldset-complete');
        }


        if (!empty($dependencies)) {
            $dependenceBlock = $sender->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
            /* @var $dependenceBlock \Magento\Backend\Block\Widget\Form\Element\Dependence */

            // for every mapping dependency that is contained in another tab, we need to add manually the parent field to the block
            if (!isset($dependency_mapping[self::EPICOR_COMM_LICENSING_ERP_KEY])) {
                $dependency_mapping[self::EPICOR_COMM_LICENSING_ERP_KEY] = self::EPICOR_COMM_LICENSING_ERP_VALUE;
            }

            if (!empty($dependency_mapping)) {
                foreach ($dependency_mapping as $fieldId => $name) {
                    $dependenceBlock->addFieldMap($fieldId, $name);
                }
            }

            foreach ($dependencies as $dependency) {
                //M1 > M2 Translation Begin (Rule 7)
                /*$dependenceBlock->addFieldDependence(
                    $dependency['field'], $dependency['depends_on'], $dependency['value']
                );*/
                if (is_array($dependency['value'])) {
                    $dependencyValue = implode(',', $dependency['value']);
                } else {
                    $dependencyValue = $dependency['value'];
                }
                $field = $this->fieldFactory->create(
                    ['fieldData' => ['value' => $dependencyValue, 'separator' => ',']]
                );
                $dependenceBlock->addFieldDependence(
                    $dependency['field'], $dependency['depends_on'], $field
                );
                //M1 > M2 Translation End
            }

            $sender->setChild('form_after', $dependenceBlock);
        }

        $form->setValues($form_data);

        return $form;
    }

    private function checkDependency($dependency)
    {
        $allow = true;

        foreach ($dependency as $configPath => $values) {
            $configValue = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (isset($values['in'])) {
                if (!in_array($configValue, $values['in'])) {
                    $allow = false;
                }
            } else if (isset($values['eq'])) {
                if ($configValue != $values['eq']) {
                    $allow = false;
                }
            }
        }

        return $allow;
    }

}