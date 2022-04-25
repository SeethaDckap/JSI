<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Sites\Edit;


/**
 * Sites edit form
 *
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Epicor\HostingManager\Model\ResourceModel\Certificate\CollectionFactory
     */
    protected $hostingManagerResourceCertificateCollectionFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Epicor\HostingManager\Model\ResourceModel\Certificate\CollectionFactory $hostingManagerResourceCertificateCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->storeSystemStore = $storeSystemStore;
        $this->hostingManagerResourceCertificateCollectionFactory = $hostingManagerResourceCertificateCollectionFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function _prepareForm()
    {
        $site = $this->_coreRegistry->registry('current_site');


        $form = $this->_formFactory->create(['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ]]);

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Site Details')));
        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'tabindex' => 1,
            'required' => true,
            'name' => 'name'
        ));

        $fieldset->addField('url', 'text', array(
            'label' => __('Url'),
            'tabindex' => 2,
            'required' => true,
            'name' => 'url',
            'note' => 'lowercase url without the http:// or ending /<br />e.g. www.example.com'
        ));

        $fieldset->addField('extra_domains', 'text', array(
            'label' => __('Extra Domains'),
            'tabindex' => 2,
            'required' => false,
            'name' => 'extra_domains',
            'note' => 'Comma-separated<br>lowercase url without the http:// or ending /<br />e.g. www.example.com,www.example1.com'
        ));

        if (!$site->getisDefault()) {
            $prefix = $site->getIsWebsite() ? 'website_' : 'store_';
            $site->setChildId($prefix . $site->getChildId());
            $fieldset->addField('child_id', 'select', array(
                'label' => __('Store'),
                'required' => true,
                'values' => $this->_getStores(),
                'name' => 'child_id'
            ));
        }
        $fieldset->addField('certificate_id', 'select', array(
            'label' => __('SSL Certificate'),
            'required' => false,
            'values' => $this->_getCertificates(),
            'name' => 'certificate_id'
        ));

        $fieldset->addField('secure', 'checkbox', array(
            'label' => __('Entire site Secure'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'checked' => ($site->getSecure() == 1) ? 'true' : '',
            'name' => 'secure',
            'note' => 'Ticking this box will ensure all pages on your site run on https'
        ));


        $form->setValues($site->getData());

        return parent::_prepareForm();
    }

    /**
     * Gets an array of visible stores, for display in a select box (optgroup nested)
     *
     * @return array - array of stores
     */
    private function _getStores()
    {
        $storeModel = $this->storeSystemStore;
        /* @var $storeModel \Magento\Store\Model\System\Store */

        $options = array();

        foreach ($storeModel->getWebsiteCollection() as $website) {
            /* @var $website Mage_Core_Model_Website */
            $websiteShow = false;
            $groupOption = array();

            foreach ($storeModel->getGroupCollection() as $group) {
                /* @var $group Mage_Core_Model_Store_Group */
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }

                $stores = array();
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    /* @var $store Epicor_Comm_Model_Store */
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }

                    if (!$websiteShow) {
                        $websiteShow = true;
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                    }

                    $stores[] = array(
                        'label' => $store->getName(),
                        'value' => 'store_' . $store->getId()
                    );
                }

                if ($groupShow) {
                    $groupOption[] = array(
                        'label' => $group->getName(),
                        'value' => $stores
                    );
                }
            }

            if ($websiteShow) {
                $options[] = array(
                    'label' => $website->getName(),
                    'value' => 'website_' . $website->getId()
                );

                if (!empty($groupOption)) {
                    $options = array_merge($options, $groupOption);
                }
            }
        }

        return $options;
    }

    /**
     * Gets an array of certificate files
     *
     * @return array - array of certificates
     */
    private function _getCertificates()
    {
        $certs = array();
        $certificates = $this->hostingManagerResourceCertificateCollectionFactory->create();
        /* @var $certificates \Epicor\HostingManager\Model\ResourceModel\Certificate\Collection */

        $certs[] = array(
            'label' => '      ', 
            'value' => '',
        );

        if (!empty($certificates)) {
            foreach ($certificates as $ssl) {
                /* @var $ssl \Epicor\HostingManager\Model\Cerficate */
                $certs[] = array(
                    'label' => $ssl->getName(),
                    'value' => $ssl->getId(),
                );
            }
        }
        return $certs;
    }

}
