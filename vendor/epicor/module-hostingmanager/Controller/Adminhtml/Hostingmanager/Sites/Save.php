<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites;

class Save extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\HostingManager\Helper\Data
     */
    protected $hostingManagerHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\HostingManager\Model\SiteFactory
     */
    protected $hostingManagerSiteFactory;

    /**
     * @var \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory
     */
    protected $hostingManagerResourceSiteCollectionFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\HostingManager\Helper\Data $hostingManagerHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\HostingManager\Model\SiteFactory $hostingManagerSiteFactory,
        \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->backendSession = $backendAuthSession;
        $this->hostingManagerHelper = $hostingManagerHelper;
        $this->storeManager = $storeManager;
        $this->hostingManagerSiteFactory = $hostingManagerSiteFactory;
        $this->hostingManagerResourceSiteCollectionFactory = $collectionFactory;
        parent::__construct($context, $hostingManagerSiteFactory, $backendAuthSession);
    }


    public function execute()
    {
        // if http: or https: submitted, return to main screen with error 
        $url = $this->getRequest()->getParam('url');
        $extraDomain = trim($this->getRequest()->getParam('extra_domains'));


        if ((stripos($url, 'http://') !== false) || (stripos($url, 'https://')) !== false) {
            $this->messageManager->addErrorMessage("Unable to save url with prefix of http:// or https://");
            //       $this->_redirect('epicor_hostingmanager/adminhtml_sites/index');
            $this->_redirect('*/*/');
            //    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("epicor_hostingmanager/adminhtml_sites/index"));
        } else {

            //validate domain name
            $regex = "/^([a-z0-9][a-z0-9\-\.]{1,63})$/i";
            if (!preg_match($regex, $url, $matches)) {
                $error = __('Unable to save url %s as it contains invalid characters, it must only contain alphanumerics, dashes "-" and full stops "."', $url);
                $this->messageManager->addErrorMessage($error);
                $this->_redirect('*/*/');
                return;
            }

            $hostingHelper = $this->hostingManagerHelper;
            /* @var @helper Epicor_HostingManager_Helper_Data */
            $checkExtraDomain = $hostingHelper->checkExtraDomain($extraDomain);
            if (isset($checkExtraDomain['status']) && $checkExtraDomain['status'] == "error") {
                $error = __('Unable to save extra domain %s as it contains ' . $checkExtraDomain['message'], $checkExtraDomain['data']);
                $this->messageManager->addErrorMessage($error);
                $this->_redirect('*/*/');
                return;
            }

            if ($data = $this->getRequest()->getPost()) {
                $id = $this->getRequest()->getParam('id', null);
                $site = $this->_loadSite($id);
                try {
                    $site->setName($data['name']);
                    $secure = isset($data['secure']) ? $data['secure'] : 0;
                    //Remove space after comma
                    $removeSpaces = preg_replace("/\s*,\s*/", ",", $data['extra_domains']);
                    $serverName = isset($data['extra_domains']) ? rtrim(trim($removeSpaces), ',') : "";
                    $site->setExtraDomains($serverName);
                    $site->setSecure($secure);
                    $site->setUrl(strtolower($data['url']));
                    if (!empty($data['certificate_id'])) {
                        $site->setCertificateId($data['certificate_id']);
                    } else {
                        $site->setCertificateId(null);
                    }

                    $doValidation = true;
                    if ($site->getIsDefault()) {
                        $doValidation = false;
                        $childId = 0;
                        $site->setCode('');
                        $site->setScope('default');
                    } elseif (strpos($data['child_id'], 'website') !== false) {
                        $childId = str_replace('website_', '', $data['child_id']);
                        $website = $this->storeManager->getWebsite($childId);
                        if ($website) {
                            $site->setCode($website->getCode());
                            $site->setIsWebsite(true);
                            $website_store_id = $website->getDefaultStore()->getId();
                            $website_id = $website->getId();
                        }
                    } else {
                        $childId = str_replace('store_', '', $data['child_id']);
                        $store = $this->storeManager->getStore($childId);
                        if ($store) {
                            $site->setCode($store->getCode());
                            $site->setIsWebsite(false);
                            $website = $store->getWebsite();
                            $website_store_id = $website->getDefaultStore()->getId();
                            $website_id = $website->getId();
                        }
                    }

                    $site->setChildId($childId);
                    if ($doValidation) {
                        $validation_error = array();
                        $url_site = $this->hostingManagerSiteFactory->create()->load($data['url'], 'url');
                        /* @var $url_site \Epicor\HostingManager\Model\Site */
                        if (
                            $url_site->getId() &&
                            $site->getId() != $url_site->getId()
                        ) {
                            $validation_error[] = __('You can not have multiple sites with the same url');
                        }

                        $site_collection = $this->hostingManagerResourceSiteCollectionFactory->create();
                        /* @var $site_collection \Epicor\HostingManager\Model\ResourceModel\Site\Collection */


                        $site_collection
                            ->getSelect()
                            ->where('((child_id = ' . $website_id . ' && is_website = 1  && ' . $childId . ' = ' . $website_store_id . ')OR (child_id = ' . $childId . ' && is_website = 0))')
                            ->where('entity_id != ?', $site->getId());

                        //                    $validation_error[] = $site_collection->getSelectSql(true);
                        //                    $validation_error[] = Mage::app()->getDefaultStoreView()->getId()." == $childId && ".var_export(!$site->getIsWebsite(), true);
                        if ($site_collection->count()) {
                            //$validation_error[] = $site_collection->getSelectSql(true);
                            $validation_error[] = __('You can not have multiple sites linking to the same store or website');
                        }

                        //M1 > M2 Translation Begin (Rule p2-6.5)
                        //if ((Mage::app()->getDefaultStoreView()->getId() == $childId && !$site->getIsWebsite()) || (Mage::app()->getDefaultStoreView()->getWebsite()->getId() == $childId && $site->getIsWebsite())) {
                        if (($this->storeManager->getDefaultStoreView()->getId() == $childId && !$site->getIsWebsite()) || ($this->storeManager->getDefaultStoreView()->getWebsiteId() == $childId && $site->getIsWebsite())) {
                            //M1 > M2 Translation End
                            $validation_error[] = __('Only the Default website can link to the default website and store view');
                        }

                        if ($validation_error) {
                            throw new \Exception(implode('<br>', $validation_error));
                        }
                    }
                    $site->save();

                    $hostingHelper->setUnsecureHttps($site);

                    if (!$site->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Site'));
                    }
                    $this->messageManager->addSuccessMessage(__('Site was successfully saved.'));
                    $this->backendSession->setFormData(false);

                    $this->_redirect('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    if ($site) {
                        $this->registry->unregister('current_site');
                        $this->registry->register('current_site', $site);
                        $this->_forward('edit');
                    } else {
                        $this->_redirect('*/*/');
                    }
                }

                return;
            }
            $this->messageManager->addErrorMessage(__('No data found to save'));
            $this->_redirect('*/*/');
        }
    }

}
