<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;

use IntlDateFormatter;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as TimezoneInterface;

/**
 * List Details Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Details extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\Lists\Model\ContractFactory
     */
    protected $listsContractFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\Factory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\SettingFactory
     */
    protected $listsListModelSettingFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\AbstractFactory
     */
    protected $listsListModelTypeAbstractFactory;

    /**
     * @var \Epicor\Comm\Model\Config\Source\YesnonulloptionFactory
     */
    protected $commConfigSourceYesnonulloptionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

         /**
     * @var \Epicor\Lists\Model\ListModel\Type\FavoriteFactory
     */
    protected $listsListModelTypeFavoriteFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\ContractFactory
     */
    protected $listsListModelTypeContractFactory;

     /**
     * @var \Epicor\Lists\Model\ListModel\Type\PredefinedFactory
     */
    protected $listsListModelTypePredefinedFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\PricelistFactory
     */
    protected $listsListModelTypePricelistFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\ProductgroupFactory
     */
    protected $listsListModelTypeProductgroupFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\RecentpurchaseFactory
     */
    protected $listsListModelTypeRecentpurchaseFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Type\RestrictedpurchaseFactory
     */
    protected $listsListModelTypeRestrictedpurchaseFactory;

    private $timezone;

    public function __construct(
        TimezoneInterface $timezone,
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Lists\Model\ContractFactory $listsContractFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Lists\Model\ListModel\SettingFactory $listsListModelSettingFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Model\ListModel\Type\AbstractModelFactory $listsListModelTypeAbstractFactory,
        \Epicor\Comm\Model\Config\Source\YesnonulloptionFactory $commConfigSourceYesnonulloptionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Lists\Model\ListModel\Type\ContractFactory $listsListModelTypeContractFactory,
        \Epicor\Lists\Model\ListModel\Type\FavoriteFactory $listsListModelTypeFavoriteFactory,
        \Epicor\Lists\Model\ListModel\Type\PredefinedFactory $listsListModelTypePredefinedFactory,
        \Epicor\Lists\Model\ListModel\Type\PricelistFactory $listsListModelTypePricelistFactory,
        \Epicor\Lists\Model\ListModel\Type\ProductgroupFactory $listsListModelTypeProductgroupFactory,
        \Epicor\Lists\Model\ListModel\Type\RecentpurchaseFactory $listsListModelTypeRecentpurchaseFactory,
        \Epicor\Lists\Model\ListModel\Type\RestrictedpurchaseFactory $listsListModelTypeRestrictedpurchaseFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->listsContractFactory = $listsContractFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->listsListModelSettingFactory = $listsListModelSettingFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsListModelTypeAbstractFactory = $listsListModelTypeAbstractFactory;
        $this->commConfigSourceYesnonulloptionFactory = $commConfigSourceYesnonulloptionFactory;
        $this->registry = $registry;
        $this->_localeResolver = $localeResolver;        
        $this->listsListModelTypeContractFactory = $listsListModelTypeContractFactory;
        $this->listsListModelTypeFavoriteFactory = $listsListModelTypeFavoriteFactory;
        $this->listsListModelTypePredefinedFactory = $listsListModelTypePredefinedFactory;
        $this->listsListModelTypePricelistFactory = $listsListModelTypePricelistFactory;
        $this->listsListModelTypeProductgroupFactory = $listsListModelTypeProductgroupFactory;
        $this->listsListModelTypeRecentpurchaseFactory = $listsListModelTypeRecentpurchaseFactory;
        $this->listsListModelTypeRestrictedpurchaseFactory = $listsListModelTypeRestrictedpurchaseFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->_title = 'Details';
        $this->timezone = $timezone;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */

        $form = $this->formFactory->create();
        $formData = $this->backendSession->getFormData(true);

        if (empty($formData)) {
            $formData = $list->getData();
        } else {
            if (is_array($formData)) {
                $list->addData($formData);
            }
        }
        $contractStatus = null;
        if ($list->getType() == "Co") {
            $contract = $this->listsContractFactory->create()->load($list->getId(), 'list_id');
            $contractStatus = ($contract->getContractStatus() == "A") ? "Active" : "Inactive";
            $formData['contact_name'] = $contract->getContactName();
            $formData['po_number'] = $contract->getPurchaseOrderNumber();
            $formData['sales_rep'] = $contract->getSalesRep();
            $formData['last_modified_date'] = $contract->getLastModifiedDate();
            $formData['last_used_time'] = $contract->getLastUsedTime();
            $formData['contract_status'] = $contractStatus;
        }

        $this->addPrimaryFields($form, $list);
        $this->addActiveFields($form, $list, $contractStatus);
        $this->addFlagFields($form, $list);
        $this->addTypeSpecificFields($form, $list);

        if ($list->isObjectNew()) {
            $this->addImportFields($form, $list);
        } else {
            $this->addErpFields($form, $list);
        }

        $formData['settings'] = $list->getSettings();
        $formData['erp_override'] = $list->getErpOverride();

        if ($list->getStartDate()) {
            $timeZoneTimeString = $this->getStrToTimeForTimeZoneFromUtc($list->getStartDate());

            $formData['start_date'] = $timeZoneTimeString;
            $formData['start_time'] = $this->getTimeFromTimeZoneString($timeZoneTimeString);
        }

        if ($list->getEndDate()) {
            $timeZoneTimeString = $this->getStrToTimeForTimeZoneFromUtc($list->getEndDate());

            $formData['end_date'] = $timeZoneTimeString;
            $formData['end_time'] = $this->getTimeFromTimeZoneString($timeZoneTimeString);
        }

        // need this line for contracts to hide uom separator
        $formData['erp_code'] = $list->getErpCode();
        $form->addValues($formData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTimeFromTimeZoneString($timeString)
    {
        $formattedTimeString = $this->getFormattedDate($timeString);
        $dateSplit = explode(' ', $formattedTimeString);
        return str_replace(':', ',', $dateSplit[1]);
    }

    private function getStrToTimeForTimeZoneFromUtc($utcDateStamp)
    {
        $formatMedium = IntlDateFormatter::MEDIUM;
        $timeStamp = $this->timezone->formatDateTime($utcDateStamp, $formatMedium, $formatMedium);
        $timeString = strtotime($timeStamp);

        return $timeString;
    }

    private function getFormattedDate($timeString)
    {
        return date(DateTime::DATETIME_PHP_FORMAT, $timeString);
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @return void
     */
    protected function addPrimaryFields($form, $list)
    {
        $fieldset = $form->addFieldset('primary', array('legend' => __('Primary Details')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $disableEdit = ($list->getType() == "Co") ? true : false;
        if ($list->getType() == "Co") {
            $fieldset->addField(
                'title', 'text', array(
                'label' => __('Title'),
                'required' => true,
                'name' => 'title',
                'readonly' => 'readonly'
                )
            );
        } else {
            $fieldset->addField(
                'title', 'text', array(
                'label' => __('Title'),
                'required' => true,
                'name' => 'title'
                )
            );
        }

        $args = array();
        if ($list->isObjectNew()) {
            $fieldset->addField(
                'listcodeurl', 'hidden', array(
                'name' => 'listcodeurl',
                'value' => $this->getUrl('adminhtml/epicorlists_list/validateCode', $args)
                )
            );
        }

        $disableFields = $list->isObjectNew() == false;

        $fieldset->addField(
            'type', 'select', array(
            'label' => __('Type'),
            'name' => 'type',
            'values' => $this->listsListModelTypeFactory->create()->toOptionArray($list->isObjectNew()),
            'disabled' => $disableFields
            )
        );

        $fieldset->addField(
            'erp_code', 'text', array(
            'label' => __('Code'),
            'required' => true,
            'name' => 'erp_code',
            'class' => $list->isObjectNew() ? 'required-entry validate-list-code' : '',
            'disabled' => $disableFields,
            'note' => __('Unique reference code for this list')
            )
        );

        $fieldset->addField(
            'code_allowed', 'hidden', array(
            'name' => 'code_allowed',
            'label' => 'code_allowed',
        ));

//        $fieldset->addField(
//            'label', 'text', array(
//            'label' => $this->__('Default Label'),
//            'required' => false,
//            'name' => 'label'
//            )
//        );

        if ($list->isObjectNew() == false) {
            $fieldset->addField(
                'source', 'text', array(
                'label' => __('Source'),
                'required' => false,
                'name' => 'source',
                'disabled' => true,
                )
            );
        }

        $fieldset->addField(
            'notes', 'textarea', array(
            'label' => __('Notes'),
            'required' => false,
            'name' => 'notes'
            )
        );
        $fieldset->addField(
            'description', 'textarea', array(
            'label' => __('Description'),
            'required' => false,
            'name' => 'description'
            )
        );
    }

    /**
     * Adds the Type specific details
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function addTypeSpecificFields($form, $list)
    {
        $fieldset = $form->addFieldset('typespecific', array('legend' => __('Type Specific Details')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $disableEdit = ($this->getList()->getType() == "Co") ? true : false;

        $fieldset->addField(
            'sales_rep', 'text', array(
            'label' => __('Sales Rep'),
            'name' => 'sales_rep',
            'disabled' => 1
            )
        );

        $fieldset->addField(
            'contact_name', 'text', array(
            'label' => __('Contact Name'),
            'name' => 'contact_name',
            'disabled' => 1
            )
        );

        $fieldset->addField(
            'po_number', 'text', array(
            'label' => __('PO Number'),
            'name' => 'po_number',
            'disabled' => $disableEdit
            )
        );

        if ($this->getList()->getType() == "Co") {
            $fieldset->addField(
                'last_modified_date', 'text', array(
                'label' => __('Last Modified Date'),
                'name' => 'last_modified_date',
                'disabled' => 1
                )
            );

            $fieldset->addField(
                'last_used_time', 'text', array(
                'label' => __('Last Used Date & Time'),
                'name' => 'last_used_time',
                'disabled' => 1
                )
            );
        }
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return void
     */
    protected function addActiveFields($form, $list, $contractStatus = null)
    {
        $fieldset = $form->addFieldset('active_fields', array('legend' => __('Active Details')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $disableEdit = ($this->getList()->getType() == "Co") ? true : false;

        $hideActiveUi = false;

        if ($list->getType() == "Co") {
            $fieldset->addField(
                'contract_status', 'text', array(
                'label' => __('ERP contract status'),
                'name' => 'contract_status',
                'disabled' => 1
                )
            );
            $hideActiveUi = ($contractStatus == "Active") ? 0 : 1;
        }

        if (empty($hideActiveUi)) {
            $fieldset->addField(
                'active', 'checkbox', array(
                'label' => __('Is Active?'),
                'tabindex' => 1,
                'value' => 1,
                'name' => 'active',
                'checked' => $this->getList()->getActive()
                )
            );

            $after = '<small class="datepicker-comments" style="margin:0 0 0 38px;">YYYY-MM-DD</small><br /><small>To Update Date Click on Calendar Icon</small>';

            $fieldset->addField(
                'start_date', 'date', array(
                'label' => __('From Date'),
                'comment' => 'Change Date Using Date Picker',
                'disabled' => $disableEdit,
                'tabindex' => 1,
                'class' => 'validate-date',
                'required' => false,
                'name' => 'start_date',
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'date_format' => 'yyyy-MM-dd',
                'after_element_html' => $after,
                )
            );
            $fieldset->addField(
                'select_start_time', 'checkbox', array(
                'label' => __('Select Start Time?'),
                'tabindex' => 1,
                'name' => 'select_start_time',
                'disabled' => $disableEdit,
                )
            );
            $fieldset->addField(
                'start_time', 'time', array(
                'label' => __('Start Time'),
                'tabindex' => 1,
                'class' => 'validate-time',
                'required' => false,
                'name' => 'start_time',
                'format' => 'hh:mm:ss',
                'comment' => 'hh:mm:ss',
                'disabled' => $disableEdit,
                )
            );

            $fieldset->addField(
                'end_date', 'date', array(
                'label' => __('To Date'),
                'comment' => 'Change Date Using Date Picker',
                'tabindex' => 1,
                'class' => 'validate-date',
                'required' => false,
                'name' => 'end_date',
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'date_format' => 'yyyy-MM-dd',
                'after_element_html' => $after,
                'disabled' => $disableEdit,
                )
            );

            $fieldset->addField(
                'select_end_time', 'checkbox', array(
                'label' => __('Select End Time?'),
                'tabindex' => 1,
                'name' => 'select_end_time',
                'disabled' => $disableEdit,
                )
            );
            $fieldset->addField(
                'end_time', 'time', array(
                'label' => __('End Time'),
                'tabindex' => 1,
                'class' => 'validate-time',
                'name' => 'end_time',
                'format' => 'hh:mm:ss',
                'comment' => 'hh:mm:ss',
                'disabled' => $disableEdit,
                )
            );

            $isEnabledJs = $fieldset->addField('is_enabled_js', 'hidden', array('name' => 'is_enabled_js'), false);
            $isEnabledJs->setAfterElementHtml($this->getEnableDisableJs());
        }
    }

    private function getEnableDisableJs()
    {
        return $this->getLayout()
            ->createBlock('\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Details')
            ->setTemplate('epicor/lists/details/time-field.phtml')
            ->_toHtml();
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @return void
     */
    protected function addFlagFields($form, $list)
    {

        $typeInstance = $list->getTypeInstance();

        $settings = $this->listsListModelSettingFactory->create();
        /* @var $settings Epicor_Lists_Model_ListModel_Setting */

        $fieldset = $form->addFieldset('settings_fields', array('legend' => __('Settings')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $disableEdit = ($this->getList()->getType() == "Co") ? true : false;

        $supported = $typeInstance->getSupportedSettings();

        if (count($supported) > 0) {
            $fieldset->addField(
                'settings', 'checkboxes', array(
                'label' => __('Settings'),
                'name' => 'settings[]',
                'options' => $settings->toOptionArray($supported),
                'checked' => $list->getSettings(),
                'disabled' => ($disableEdit) ? $supported : 0,
                )
            );
        }

        $fieldset->addField(
            'priority', 'text', array(
            'label' => __('Priority'),
            'name' => 'priority',
            'class' => 'validate-number',
            'disabled' => $disableEdit
            )
        );

        if (($list->getSource() == "customer") && ($list->getOwnerId() != "")) {
            $Customer = $this->customerCustomerFactory->create();
            $getCreatedBy = $Customer->load($list->getOwnerId());
            $emailId = $getCreatedBy->getEmail();
            $fieldset->addField('ownerids', 'label', array(
                'label' => __('Created By'),
                'name' => 'ownerids',
                'value' => $emailId,
                'disabled' => 1
            ));
        }

        if ($list->isObjectNew()) {
            $typeModel = $this->listsListModelTypeFactory->create();
            /* @var $typeModel Epicor_Lists_Model_ListModel_Type */

            $instance = $this->listsListModelTypeAbstractFactory->create();
            /* @var $instance Epicor_Lists_Model_ListModel_Type_AbstractModel */

            $fieldset->addField(
                'supported_settings_all', 'hidden', array(
                'name' => 'supported_settings_all',
                'value' => implode('', $instance->getSupportedSettings())
                )
            );

            foreach ($typeModel->getTypeInstances() as $type => $instanceName) {
                 /* To fix dynamic loading of corresponding model instance */
                $instanceName = ucfirst($instanceName);
                $modelFactory = "listsListModelType{$instanceName}Factory";
                $instance = $this->$modelFactory->create();
                /* @var $instance Epicor_Lists_Model_ListModel_Type_AbstractModel */
                $fieldset->addField(
                    'supported_settings_' . $type, 'hidden', array(
                    'name' => 'supported_settings_' . $type,
                    'value' => implode('', $instance->getSupportedSettings())
                    )
                );
            }
        }
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return void
     */
    protected function addImportFields($form, $list)
    {
        if ($list->getId()) {
            $fieldset = $form->addFieldset('import_fields', array('legend' => __('Product Import')));
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */

            $fieldset->addField('productimportcsv', 'button', array(
                'value' => __('Download Example CSV File'),
                'onclick' => "return window.location = '" . $this->getUrl('epicor_lists/epicorlists_lists/productimportcsv') . "';",
                'name' => 'productimportcsv',
                'class' => 'form-button'
            ));

            $fieldset->addField(
                'import', 'file', array(
                'label' => __('CSV File'),
                'name' => 'import',
                'note' => __('CSV containing 2 columns: "SKU" (required), "UOM" (optional). If no UOM provided, all UOMs for SKU will be added')
                )
            );
        }   
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @return void
     */
    protected function addErpFields($form, $list)
    {
        $typeInstance = $list->getTypeInstance();

        if ($typeInstance && $typeInstance->hasErpMsg()) {
            $msgName = $typeInstance->getErpMsg();
            //M1 > M2 Translation Begin (Rule 55)
            //$legend = array('legend' => $this->__('Overwritten On %s Update', $msgName));
            $legend = array('legend' => __('Overwritten On %1 Update', $msgName));
            //M1 > M2 Translation End
            $fieldset = $form->addFieldset('erp_override_fields', $legend);
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */

            $erpOverride = $list->getErpOverride();

            $msgSections = $typeInstance->getErpMsgSections();
            foreach ($msgSections as $value => $title) {
                $fieldset->addField('erp_override_' . $value, 'select', array(
                    'label' => $title,
                    'name' => 'erp_override[' . $value . ']',
                    'values' => $this->commConfigSourceYesnonulloptionFactory->create()->toOptionArray(),
                    'value' => isset($erpOverride[$value]) ? $erpOverride[$value] : null
                ));
            }
        }
    }

    /**
     * Gets the current List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!isset($this->_list)) {
            $this->_list = $this->registry->registry('list');
        }
        return $this->_list;
    }

}
