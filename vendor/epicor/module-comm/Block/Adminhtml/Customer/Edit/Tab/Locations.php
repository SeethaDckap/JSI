<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;

class Locations extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'epicor_comm/customer/tab/locations.phtml';

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    protected $_customerModel;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\Customer $customer,
        array $data = []
    )
    {
        
        $this->customer = $customer;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->getCustomer();
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Locations');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Locations');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }


    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $types = array('guest','supplier');
        $accountType = $this->_customerModel->getEccErpAccountType();
        $show = in_array($accountType,$types) ? FALSE : TRUE;
        return $show;
    }
    
    public function isHidden()
    {
        return false;
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        if (!$this->_customerModel) {
            $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            $this->_customerModel = $this->customer->load($customerId);
        }
        return $this->_customerModel;
    }

    
    /**
     * Initialize the form.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        //$form->setHtmlIdPrefix('_newsletter');
        $customer = $this->_customerModel;
        $linkType = $customer->getEccLocationLinkType();
        
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Location')]);

        $fieldset->addField(
            'locations_source',
            'select',
            [
                'label' => __('Location Restrictions Source'),
                'name' => 'locations_source',
                'data-form-part' => $this->getData('target_form'),
                'values' => $this->_getOptions()
            ]
        );
       $fieldset->addField('in_locations_lists_grid', 'hidden',
               ['name' => 'in_locations_lists_grid','data-form-part' => $this->getData('target_form'), 'id' => 'in_locations_lists_grid']);
       $fieldset->addField('in_locations_lists_grid_old', 'hidden', ['name' => 'in_locations_lists_grid_old']);
       
        $data = array(
            'locations_source' => ($linkType=='customer') ? 'customer' : 'erp'
        );
        
        $form->setValues($data);

        $this->setForm($form);
        return $this;
    }

    /**
     * Gets an array of options for the dropdown
     * 
     * @return array
     */
    private function _getOptions()
    {
        $options = array();

        $options[] = array(
            'label' => __('Use ERP Account Specific Locations'),
            'value' => 'erp'
        );

        $options[] = array(
            'label' => __('Use Customer Specific Locations'),
            'value' => 'customer'
        );


        return $options;
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Epicor\Comm\Block\Adminhtml\Customer\Edit\Tab\Locations\Grid',
                'locations.grid'
            )
        );
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

}
