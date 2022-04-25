<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Labels Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Labels extends \Magento\Backend\Block\Widget\Form
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
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->backendSession = $context->getBackendSession();
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Labels';
    }

    /**
     * Builds List Labels Form
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Labels
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

        $fieldset = $form->addFieldset('default', array('legend' => __('Default Label')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $fieldset->addField(
            'label', 'text', array(
            'label' => __('Default'),
            'required' => false,
            'name' => 'label',
            'disabled' => true,
            'note' => __('Default label for this list')
            )
        );

        $this->addWebsites($form);

        $labels = $list->getLabels();
        $sortedLabels = $list->getSortedLabels();

        foreach ($sortedLabels as $type => $ids) {
            foreach ($ids as $typeId => $labelId) {
                $label = $labels[$labelId];
                /* @var $label Epicor_Lists_Model_ListModel_Label */
                $formData['label_' . $type . '_' . $typeId] = $label->getLabel();
            }
        }

        $form->setValues($formData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Adds Website fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @return void
     */
    protected function addWebsites($form)
    {
        $storeModel = $this->storeSystemStore;
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */

        foreach ($storeModel->getWebsiteCollection() as $website) {
            /* @var $website Mage_Core_Model_Website */

            $groups = $website->getGroupCollection();
            if ($groups->count() > 0) {
                $this->addStoreGroupFields($form, $website, $groups->getItems());
            }
        }
    }

    /**
     * Adds Store Group specific fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Lists\Model\ListModel $list
     * @param \Magento\Store\Model\Website $website
     * @param array $groups
     *
     * @return void
     */
    protected function addStoreGroupFields($form, $website, $groups)
    {
        $fieldset = $form->addFieldset('website_' . $website->getId(), array('legend' => $website->getName()));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset->addType('heading', 'Epicor_Common_Lib_Varien_Data_Form_Element_Heading');

        $webNameBase = 'labels[' . $website->getId() . ']';
        $fieldName = $webNameBase . '[default]';

        $this->addLabelField(
            $fieldset, 'websites', $website->getId(), $fieldName, __('Default for Website')
        );

        foreach ($groups as $group) {

            $groupNameBase = $webNameBase . '[groups][' . $group->getId() . ']';

            /* @var $group Mage_Core_Model_Store_Group */
            $this->addGroupHeading(
                $fieldset, $group->getId(), $group->getName()
            );

            $collection = $group->getStoreCollection();
            if ($collection->count() > 1) {
                $fieldName = $groupNameBase . '[default]';
                $this->addLabelField(
                    $fieldset, 'store_groups', $group->getId(), $fieldName, __('Default for Store')
                );
                $this->addStoreViewHeading($fieldset, $group->getId());
            }

            foreach ($collection->getItems() as $store) {
                /* @var $store Mage_Core_Model_Store */
                $fieldName = $groupNameBase . '[stores][' . $store->getId() . ']';
                $this->addLabelField(
                    $fieldset, 'stores', $store->getId(), $fieldName, $store->getName()
                );
            }
        }
    }

    /**
     * Adds a label field to the display
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $type
     * @param integer $id
     * @param string $fieldName
     * @param string $label
     */
    protected function addLabelField($fieldset, $type, $id, $fieldName, $label)
    {
        $fieldset->addField(
            'label_' . $type . '_' . $id, 'text', array(
            'label' => $label,
            'name' => $fieldName,
            )
        );
    }

    /**
     * Adds a label field to the display
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param integer $id
     * @param string $name
     */
    protected function addGroupHeading($fieldset, $id, $name)
    {
        $fieldset->addField(
            'group_heading_' . $id, 'heading', array(
            'label' => __('Store: ') . $name
        ));
    }

    /**
     * Adds a label field to the display
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param integer $id
     */
    protected function addStoreViewHeading($fieldset, $id)
    {
        $fieldset->addField(
            'storeview_heading_' . $id, 'heading', array(
            'label' => __('Store Views'),
            'subheading' => true
        ));
    }

}
