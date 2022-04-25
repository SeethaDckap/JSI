<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions;

use Epicor\Lists\Model\ListModel\Address\Restriction;
use Magento\Backend\Block\Widget\Button;

/**
 * List ERP Accounts Restricted address form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Lists\Model\ListModel\Address
     */
    protected $address;
    
    /**
     * @var \Epicor\Lists\Model\ListModel
     */
    protected $list;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $directoryConfigSourceCountryFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\SelectFactory
     */
    protected $formElementSelectFactory;
    
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\SelectFactory $formElementSelectFactory,
        \Magento\Directory\Helper\Data $directoryData,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->formElementSelectFactory = $formElementSelectFactory;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->listsHelper = $listsHelper;
        $this->directoryConfigSourceCountryFactory = $directoryConfigSourceCountryFactory;
        $this->directoryData=$directoryData;
        parent::__construct(
            $context,
            $data
        );

        $this->_title = $this->getTitleString();
    }

    /**
     * Returns a string for use in the title
     *
     * @return string
     */
    public function getTitleString()
    {
        $restrictionType = $this->getRequest()->getParam('restrictionTypeValue');

        $buttonType = $this->getRequest()->getParam('buttonType');

        if ($buttonType == 'add') {
            $title = 'New ';
        } else {
            $title = 'Updating ';
        }

        switch ($restrictionType) {
            case Restriction::TYPE_ADDRESS:
                $title .= 'Address Restriction';
                break;
            case Restriction::TYPE_COUNTRY:
                $title .= 'Country Restriction';
                break;
            case Restriction::TYPE_STATE:
                $title .= 'State Restriction';
                break;
            case Restriction::TYPE_ZIP:
                $title .= 'Zip Restriction';
                break;
        }

        return $title;
    }

    /**
     * Gets the List for this tab
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!$this->list) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('list_id'));
            }
        }
        return $this->list;
    }

    /**
     * Gets the address for this form
     *
     * @return \Epicor\Lists\Model\ListModel\Address
     */
    public function getAddress()
    {
        if (!$this->address) {
            if ($this->registry->registry('address')) {
                $this->address = $this->registry->registry('address');
            } else {
                $this->address = $this->listsListModelAddressFactory
                    ->create()->load($this->getRequest()->getParam('address_id'));
            }
        }
        return $this->address;
    }

    /**
     * Builds restriction address form
     *
     * @return form
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $address = $this->getAddress();

        /* @var $list Epicor_Lists_Model_ListModel */
        $restrictionType = $this->getRequest()->getParam('restrictionTypeValue');

        $buttonType = $this->getRequest()->getParam('buttonType');
        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('restrictions_form', array('legend' => $this->getTitleString()));

        $fieldset->setHeaderBar(
            '<button title="' . __('Close') . '" type="button" class="scalable" onclick="closeRestrictionForm();"><span><span><span>' . __('Close') . '</span></span></span></button>'
        );

        $fieldset->addField('address_post_url', 'hidden', array(
            'name' => 'post_url',
            'value' => $this->getUrl('epicor_lists/epicorlists_lists/restrictedaddresspost')
        ));

        $fieldset->addField('restriction_type', 'hidden', array(
            'name' => 'restriction_type',
            'value' => $restrictionType
        ));

        $fieldset->addField('address_delete_url', 'hidden', array(
            'name' => 'delete_url',
            'value' => $this->getUrl('adminhtml/epicorlists_list/addressdelete')
        ));

        $fieldset->addField('list_id', 'hidden', array(
            'name' => 'list_id',
            'value' => $this->getList()->getId()
        ));

        $fieldset->addField('address_id', 'hidden', array(
            'name' => 'address_id',
            'value' => $address->getId()
        ));

        $this->addTypeFields($restrictionType, $form, $fieldset);

        $form->addValues($address->getData());

        if ($buttonType == 'add') {
            $fieldset->addField('addSubmit', 'submit', array(
                'value' => __('Add'),
                'onclick' => "saveRestrictionAddress();return false;",
                'name' => 'addSubmit',
                'class' => 'form-button'
            ));
        } else {
            $fieldset->addField('updateSubmit', 'submit', array(
                'value' => __('Update'),
                'onclick' => "saveRestrictionAddress(this);return false;",
                'name' => 'updateSubmit',
                'class' => 'form-button'
            ));
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Adds fields to the form for the restriction type
     *
     * @param string $restrictionType
     * @param \Magento\Framework\Data\Form $form
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function addTypeFields($restrictionType, $form, $fieldset)
    {
        switch ($restrictionType) {
            case Restriction::TYPE_ADDRESS:
                $this->addAddressFields($fieldset);
                $this->addCountry($fieldset, $restrictionType);
                $this->addCounty($form, $fieldset);
                $this->addPostcode($fieldset);

                break;
            case Restriction::TYPE_COUNTRY:
                $this->addCountry($fieldset, $restrictionType);
                break;
            case Restriction::TYPE_STATE:
                $this->addCountry($fieldset, $restrictionType);
                $this->addCounty($form, $fieldset);
                break;
            case Restriction::TYPE_ZIP:
                $this->addCountry($fieldset, $restrictionType);
                $this->addPostcode($fieldset, true);
                break;
        }
    }

    /**
     * Adds address fields to the form
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function addAddressFields($fieldset)
    {
        $address = $this->getAddress();

        $fieldset->addField('address_name', 'text', array(
            'label' => __('Customer Name'),
            'required' => false,
            'name' => 'name',
            'value' => $address->getName(),
            'after_element_html' => '<small>Expected format: Firstname Lastname</small>'
        ));

        $fieldset->addField('address1', 'text', array(
            'label' => __('Address 1'),
            'required' => false,
            'name' => 'address1',
            //M1 > M2 Translation Begin (Rule 9)
            //'value' => $address->getAddress1()
            'value' => $address->getData('address1')
            //M1 > M2 Translation End
        ));

        $fieldset->addField('address2', 'text', array(
            'label' => __('Address 2'),
            'required' => false,
            'name' => 'address2',
            //M1 > M2 Translation Begin (Rule 9)
            //'value' => $address->getAddress2()
            'value' => $address->getData('address2')
            //M1 > M2 Translation End
        ));

        $fieldset->addField('address3', 'text', array(
            'label' => __('Address 3'),
            'required' => false,
            'name' => 'address3',
            //M1 > M2 Translation Begin (Rule 9)
            //'value' => $address->getAddress3()
            'value' => $address->getData('address3')
            //M1 > M2 Translation End
        ));

        $fieldset->addField('city', 'text', array(
            'label' => __('City'),
            'required' => false,
            'name' => 'city',
            'value' => $address->getCity()
        ));
    }

    /**
     * Adds country field to the form
     *
     * @param $fieldset
     * @param $restrictionType
     */
    protected function addCountry($fieldset, $restrictionType)
    {
        $selectType = $restrictionType === Restriction::TYPE_COUNTRY ? 'multiselect' : 'select';
        $country = $fieldset->addField('country', $selectType, array(
            'label' => __('Country'),
            'required' => true,
            'name' => 'country',
            'values' => $this->getCountryOptions(),
            'class' => 'countries validate-select'
        ));
        
        if ($restrictionType == Restriction::TYPE_ADDRESS || $restrictionType == Restriction::TYPE_STATE) {
            /*
             * Add Ajax to the Country select box html output
             */
            $country->setAfterElementHtml("<script type=\"text/javascript\">
                //<![CDATA[
                new RegionUpdater('country', 'county', 'county_id', " . $this->directoryData->getRegionJson() . ", undefined, undefined);
                //]]>
            </script>");
        }
    }

    private function getCountryOptions()
    {
        $options = $this->directoryConfigSourceCountryFactory->create()->toOptionArray();
        array_shift($options);
        return $options;
    }

    /**
     * Adds county field to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function addCounty($form, $fieldset)
    {
        $address = $this->getAddress();
        $county_id = $this->formElementSelectFactory->create(array(
            'label' => '',
            'required' => true,
            'name' => 'county_id',
            'no_display' => true,
        ));
        $county_id->setForm($form);
        $county_id->setId('county_id');
        $county_id->setName('county_id');
        
        $fieldset->addField('county', 'text', array(
            'label' => __('County'),
            'required' => true,
            'class' => 'check-empty',
            'name' => 'county',
            'value' => $address->getCounty(),
            'after_element_html' => $county_id->getElementHtml()
        ));
    }

    /**
     * Adds postcode field to the form
     * @param $validate
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function addPostcode($fieldset, $validate = false)
    {
        $address = $this->getAddress();
        $fieldset->addField('Postcode', 'text', array(
            'label' => __('Postcode'),
            'required' => $validate ? true : false,
            'class' => $validate ? 'check-empty' : '',
            'name' => 'postcode',
            'value' => $address->getPostcode()
        ));
    }

}
