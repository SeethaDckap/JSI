<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions;


/**
 * List Restricted type form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Types extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->backendSession = $context->getBackendSession();
        $this->backendAuthSession = $backendAuthSession;
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Restricted Purchase';
    }

    /**
     * Builds List ERP Accounts Form
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts\Form
     */
    protected function _prepareForm()
    {
        $list = $this->registry->registry('list');
        /* @var $list Epicor_Lists_Model_ListModel */
        $form = $this->formFactory->create();
        $formData = $this->backendSession->getFormData(true);
        if (empty($formData)) {
            $formData = $list->getData();
        }
        if ($this->backendAuthSession->getRestrictionTypeValue()) {
            $formData['restriction_type'] = $this->backendAuthSession->getRestrictionTypeValue();
        }
        $fieldset = $form->addFieldset('restricted_purchases_type_form', array('legend' => __('Restricted Purchase')));
        $fieldset->addField('restriction_type', 'select', array(
                'label' => __('Restricted Purchase Type'),
                'required' => false,
                'name' => 'restriction_type',
                'onchange' => 'loadRestrictionsGrid(this.value)',
                'values' => array(
                    array(
                        'label' => __('Address'),
                        'value' => \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ADDRESS,
                    ),
                    array(
                        'label' => __('Country'),
                        'value' => \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_COUNTRY,
                    ),
                    array(
                        'label' => __('State'),
                        'value' => \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_STATE,
                    ),
                    array(
                        'label' => __('Zip'),
                        'value' => \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ZIP,
                    ),
                ),
                array(
                    'label' => __('Country'),
                    'value' => 'C',
                ),
                array(
                    'label' => __('State'),
                    'value' => 'S',
                ),
                array(
                    'label' => __('Zip'),
                    'value' => 'Z',
                ),
            )
        )->setAfterElementHtml('<input type="hidden" value="' . $this->backendHelper->getUrl("adminhtml/epicorlists_list/restrictionsessionset/", array()) . '" name="ajax_url" id="ajax_url" /> <input type="hidden" value="' . $this->backendHelper->getUrl("adminhtml/epicorlists_list/addupdate/", array()) . '" name="form_url" id="form_url" /> <input type="hidden" value="' . $this->backendHelper->getUrl("adminhtml/epicorlists_list/addressdelete") . '" name="delete_url" id="delete_url" />');
        $selectedPurchaseType = $formData['restriction_type'];
        if ($selectedPurchaseType) {
            $this->backendAuthSession->setPurchaseTypeValue($selectedPurchaseType);
        } else {
            $this->backendAuthSession->setPurchaseTypeValue('');
        }
        $form->setValues($formData);
        $this->setForm($form);
        return parent::_prepareForm();
    }


    public function getBackendSession()
    {
        //M1 > M2 Translation Begin (Rule p2-5.1)
        //return Mage::getSingleton('customer/session');
        return $this->backendAuthSession;
        //M1 > M2 Translation End
    }
}
