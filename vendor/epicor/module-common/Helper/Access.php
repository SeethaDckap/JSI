<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;

use Epicor\Common\Model\Access\Group;
use Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory;
use Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory as CustomerCollectionFactory;
use Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory as GroupCollectionFactory;
use Epicor\Common\Model\Url;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Store\Model\ScopeInterface;

/**
 *
 * Access helper - common functions related to Access Management
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Access extends Data
{
    /* @var $_protectedLinks
     * links that canAccessUrl will let through, because either suppliers / not logged in customers need them
     */

    private $supplierProtectedLinks = array(
        'customer/section/load/',
        'customer/account/logout/',
        'newsletter/manage/',
        'newsletter/manage/save/',
        'customer/account/edit/',
        'customer/account/editPost/',
        'customer/account/edit/changepass'
    );
    private $protectedUrls = array(
        'customer/account/login/',
        'b2b/portal/login/',
        'b2b/portal/login/access/denied/',
        'b2b_admin/portal/login/',
        'b2b_admin/portal/login/access/denied/',
        'b2b_admin/portal/register/',
        'b2b_admin/portal/registerpost/',
        'customer/account/loginPost/',
        'customer/account/forgotpassword/',
        'customer/account/forgotpasswordpost/',
        'customer/account/changeforgotten/',
        'customer/account/resetpassword/',
        'customer/account/resetpasswordpost/',
        'contacts/',
        'contacts/index/post/',
        'customer/account/createpassword/',
        'customer/account/createpasswordpost/',
        'customer/section/load/',
    );
    private $_customerconnectPortalUrls = array(
        'b2b_portal_register',
        'catalog_category_view',
        'b2b_portal_registerpost',
    );


    /**
     * @var CollectionFactory
     */
    protected $commonResourceAccessElementCollectionFactory;

    /**
     * @var Group
     */
    protected $commonAccessGroup;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory
     */
    protected $commonResourceAccessGroupCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory
     */
    protected $commonResourceAccessGroupCollectionFactory;


    private $cacheState;

    /**
     * @var Url
     */
    protected $url;


    public function __construct(
        Context $context,
        CollectionFactory $commonResourceAccessElementCollectionFactory,
        Group $commonAccessGroup,
        CustomerCollectionFactory $commonResourceAccessGroupCustomerCollectionFactory,
        GroupCollectionFactory $commonResourceAccessGroupCollectionFactory,
        StateInterface $state,
        Url $url
    ) {
        
        $this->commonResourceAccessElementCollectionFactory = $commonResourceAccessElementCollectionFactory;
        $this->commonAccessGroup = $commonAccessGroup;
        $this->commonResourceAccessGroupCustomerCollectionFactory = $commonResourceAccessGroupCustomerCollectionFactory;
        $this->commonResourceAccessGroupCollectionFactory = $commonResourceAccessGroupCollectionFactory;
        $this->cacheState = $state;
        $this->url = $url;
        parent::__construct($context);
    }
    /**
     * Checks to see if a given module + controller + action + block + action type is
     * in the excluded list of element
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $block
     * @param string $actionType
     * @param string $exclusionType
     *
     * @return boolean
     */
    public function isExcludedAccess(
        $module,
        $controller,
        $action,
        $block = '',
        $actionType = 'Access',
        $exclusionType = ''
    ) {
        $excluded = false;
        $excludedCache = false;

        $key = $module . $controller . $action . $block . $actionType;

        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('access')) {
        if ($this->cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            /* @var $cache \Magento\Framework\App\CacheInterface */
            $cacheKey = 'ELEMENT_' . $key . '_EXCLUDED';

            if (!empty($exclusionType)) {
                $cacheKey .= '_' . strtoupper($exclusionType);
            }

            $excludedCache = $cache->load($cacheKey);
        }
        //M1 > M2 Translation End
        if ($excludedCache === false || is_null($excludedCache)) {
            $excludedKey = 'excluded';
            if (!empty($exclusionType)) {
                $excludedKey = strtolower($exclusionType) . '_' . $excludedKey;
            }

            $collection = $this->commonResourceAccessElementCollectionFactory
                ->create()
                ->addFieldToFilter('module', $module)
                ->addFieldToFilter('controller', array('in' => array($controller, '*')))
                ->addFieldToFilter('action', array('in' => array($action, '*')))
                ->addFieldToFilter('block', array('in' => array($block, '*')))
                ->addFieldToFilter('action_type', array('in' => array($actionType, '*')))
                ->addFieldToFilter($excludedKey, 1);
            /* @var $collection \Epicor\Common\Model\ResourceModel\Access\Element\Collection */
            $element = $collection->getFirstItem();

            if ($element) {
                if ($element->getData($excludedKey)) {
                    $excluded = true;
                }

                //M1 > M2 Translation Begin (Rule 12)
                //if (Mage::app()->useCache('access')) {
                if ($this->cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {
                    //$cache = Mage::app()->getCacheInstance();
                    $cache = $this->cache;
                    /* @var $cache Mage_Core_Model_Cache */
                    //M1 > M2 Translation End
                    $cacheKey = 'ELEMENT_' . $key . '_EXCLUDED';
                    $cacheTag = 'EXCLUSIONS';

                    if (!empty($exclusionType)) {
                        $cacheKey .= '_' . strtoupper($exclusionType);
                        $cacheTag .= '_' . strtoupper($exclusionType);
                    }

                    $lifeTime = $this->scopeConfig->getValue(
                        'epicor_common/accessrights/cache_lifetime',
                        ScopeInterface::SCOPE_STORE
                    );
                    $cache->save($element->getData($excludedKey), $cacheKey, array('ACCESS', $cacheTag), $lifeTime);
                }
            }
        } else {
            $excluded = ($excludedCache) ? true : false;
        }

        return $excluded;
    }

    /**
     * Checks to see if the current customer has access to a given module + controller + action + block + action type
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $block
     * @param string $actionType
     *
     * @return boolean
     */
    public function customerHasAccess($module, $controller, $action, $block = '', $actionType = 'Access')
    {
        
        $registry = $this->registry->registry('customerHasAccess');

        if (is_null($registry)) {
            $registry = array();
        }

        $checkKey = $module . $controller . $action . $block . $actionType;

        if (isset($registry[$checkKey])) {
            return $registry[$checkKey];
        }

        if ($this->canAccess($module)) {
            $allowed = false;

            if (!$this->isExcludedAccess($module, $controller, $action, $block, $actionType)) {
                $groups = $this->getSessionAccessGroups();

                if (!empty($groups)) {
                    $groupModel = $this->commonAccessGroup;
                    /* @var $groupModel \Epicor\Common\Model\Access\Group */

                    foreach ($groups as $groupId) {
                        // load from cache, if no cache
                        $allowed = $groupModel
                            ->groupHasRight($module, $controller, $action, $block, $actionType, $groupId);
                        if ($allowed) {
                            break;
                        }
                    }
                } else {
                    $allowed = false;
                }
            } else {
                $allowed = true;
            }
        } else {
            $allowed = true;
        }

        $registry[$checkKey] = $allowed;

        $this->registry->unregister('customerHasAccess');
        $this->registry->register('customerHasAccess', $registry, true);

        return $allowed;
    }

    /**
     * @return array|mixed
     */
    public function getSessionAccessGroups()
    {
        $registry = $this->registry->registry('getSessionAccessGroups');

        if (!empty($registry)) {
            return $registry;
        }

        $customerSession = $this->customerSessionFactory->create();
        /* @var $customerSession \Magento\Customer\Model\Session*/

        if ($customerSession->isLoggedIn()) {
            $groups = array();

            $customer = $customerSession->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */

            $collection = $this->commonResourceAccessGroupCustomerCollectionFactory->create();
            /* @var $customer \Epicor\Common\Model\ResourceModel\Access\Group\Customer\Collection */

            $collection->addFieldToFilter('customer_id', $customer->getId());

            foreach ($collection->getItems() as $group) {
                $groups[] = $group->getGroupId();
            }

            if (empty($groups)) {
                if ($customer->isCustomer() || $customer->isSupplier()) {
                    if ($customer->isCustomer()) {
                        $groups = $this->scopeConfig
                            ->getValue('epicor_common/accessrights/customer_default', ScopeInterface::SCOPE_STORE);
                        $groups = !empty($groups) ? explode(',', $groups) : array();
                    }

                    if ($customer->isSupplier()) {
                        $groups = $this->scopeConfig
                            ->getValue('epicor_common/accessrights/supplier_default', ScopeInterface::SCOPE_STORE);
                        $groups = !empty($groups) ? explode(',', $groups) : array();
                    }
                } else {
                    $groups = $this->scopeConfig
                        ->getValue('epicor_common/accessrights/b2c_default', ScopeInterface::SCOPE_STORE);
                    $groups = !empty($groups) ? explode(',', $groups) : array();
                }

                $transport = $this->dataObjectFactory->create();
                $transport->setGroups($groups);

                $this->_eventManager->dispatch('epicor_common_get_default_session_access_groups', array(
                    'customer' => $customer,
                    'transport' => $transport
                ));

                $groups = $transport->getGroups();
            }
        } else {
            $groups = $this->scopeConfig->getValue('epicor_common/accessrights/guest', ScopeInterface::SCOPE_STORE);
            $groups = !empty($groups) ? explode(',', $groups) : array();
        }

        $this->registry->unregister('getSessionAccessGroups');
        $this->registry->register('getSessionAccessGroups', $groups, true);

        return $groups;
    }

    /**
     * Gets the access groups for the given / current ERP account
     * @param $erpAccount
     * @return array
     */
    public function getAccessGroupsForErpAccount($erpAccount = null)
    {
        $collection = $this->commonResourceAccessGroupCollectionFactory->create();

        if (!$erpAccount) {
            $helper = $this->commHelper;
            $erpAccount = $helper->getErpAccountInfo();

            $collection->addFieldToFilter(
                'erp_account_id',
                [['eq' => $erpAccount->getId()], ['null' => '']]
            );

            if ($erpAccount->isTypeSupplier()) {
                $collection->addFieldToFilter('type', 'supplier');
            } elseif ($erpAccount->isTypeCustomer()) {
                $collection->addFieldToFilter('type', 'customer');
            }
        }

        return $collection->getItems();
    }

    /**
     * Checks to see if the currently logged in user can access urls
     * @param boolean $supplierCannotAccessCustomer
     * @param string $url - url to check
     * @param boolean $skipAccessRightsCheck - whether to ckip access rights check
     *
     * @return boolean
     */
    public function canAccessUrl($url, $skipAccessRightsCheck = false, $supplierCannotAccessCustomer = false)
    {
        $registry = $this->registry->registry('canAccessUrl');

        if (is_null($registry)) {
            $registry = [];
        }

        $encodedUrl = base64_encode($url);

        if (isset($registry[$encodedUrl])) {
            return $registry[$encodedUrl];
        }

        $customerSession = $this->customerSessionFactory->create();
        /** @var \Epicor\Comm\Model\Customer $customer */
        $customer = $customerSession->getCustomer();
        $urlModuleInfo = $this->getModuleInfoFromUrl($url);
        $allowed = true;
        $fullActionName = $this->request->getFullActionName();
        if ($this->isValidModuleInfo($urlModuleInfo)) {
            if (strpos($url, 'supplierconnect') !== false) {
                $allowed = ($customer->isSupplier()) ? $this->isLicensedFor(array('Supplier')) : false;
            } elseif (strpos($url, 'customerconnect') !== false) {
                $allowed = ($customer->isCustomer()) ? $this->isLicensedFor(array('Customer')) : false;
                if(strpos($url, 'arpayments') !== false){
                    $allowed = $this->isCentralCollection($customer);
                }
            } else if (in_array($fullActionName, $this->_customerconnectPortalUrls)) {
                $allowed = $this->isLicensedFor(array('Customer'));
            } else if (strpos($url, 'dealerconnect') !== false) {
                //$allowed = ($customer->isCustomer()) ? $this->isLicensedFor(array('Customer')) : false;
                if($customer->isCustomer() && ($customer->isDealer() || $customer->isDistributor())){
                      $allowed =  $this->isLicensedFor(array('Dealer_Portal'));
                } else {
                    $allowed = false;
                }
            } else {
                if ($customerSession->isLoggedIn()) {
                    if ($customer->isSupplier()) {
                        $allowed = $this->isSupplierLicenseAllowed($supplierCannotAccessCustomer, $url);
                    } elseif ($customer->isGuest()) {
                        $allowed = $this->isLicensedFor(['Consumer']);
                    } elseif ($customer->isCustomer()) {
                        $allowed = $this->isLicensedFor(['Customer']);
                    }
                } else {
                    if ($this->canSkipAccessRights($url)) {
                        $skipAccessRightsCheck = true;
                    } else {
                        $allowed = $this->isLicensedFor(['Consumer', 'Customer']);
                    }
                }
            }
        }

        if ($this->scopeConfig->isSetFlag('epicor_common/accessrights/active', ScopeInterface::SCOPE_STORE)) {
            if (!empty($url) && !$skipAccessRightsCheck && $allowed) {
                $allowed = $this->customerHasAccess(
                    $urlModuleInfo['module'],
                    $urlModuleInfo['controller'],
                    $urlModuleInfo['action']
                );

                if (strpos($url, 'supplierconnect/password/') !== false) {
                    $allowed = true;
                }
            }
        } else {
            if (strpos($url, '/access_management/') !== false) {
                $allowed = false;
            }
        }

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setAllowed($allowed);
        $this->_eventManager->dispatch(
            'epicor_common_can_access_url_after',
            ['url' => $url, 'url_module_info' => $urlModuleInfo, 'transport' => $transportObject]
        );

        $allowed = $transportObject->getAllowed();
        $registry[$encodedUrl] = $allowed;

        $this->registry->unregister('canAccessUrl');
        $this->registry->register('canAccessUrl', $registry, true);

        return $allowed;
    }

    private function isValidModuleInfo($urlModuleInfo)
    {
        // It should validate for all Epicor, Magento and 3rd party packages.
        return true;

    }

    private function canSkipAccessRights($url)
    {
        return in_array($url, $this->protectedUrls)
            || strpos($url, 'comm/data') !== false
            || strpos($url, 'erpsimulator') !== false;
    }

    private function isSupplierLicenseAllowed($supplierCannotAccessCustomer, $url)
    {
        if($url) {
            if ((strpos($url, 'changepass') !== false) || (strpos($url, 'file/request') !== false)) {
                return true;
            }
        }
        return !$supplierCannotAccessCustomer && $this->isSupplierProtectedUrl($url)
            ? $this->isLicensedFor(array('Supplier')) : false;
    }

    public function getModuleInfoFromUrl($url)
    {
        $module = null;
        $controller = null;
        $action = null;
        $route = $this->request->getRouteName();
        if (!empty($url)) {
            $urlObj = $this->url->parseUrl($url);
            /* @var $url Url */

            $urlParts = explode('/', ltrim($urlObj->getPath(), '/'));
            $controller = (isset($urlParts[1]) && !empty($urlParts[1])) ? ucfirst($urlParts[1]) : 'Index';
            $action = (isset($urlParts[2]) && !empty($urlParts[2])) ? $urlParts[2] : 'index';

            //M1 > M2 Translation Begin (Rule 47)
            /*$module = Mage::getConfig()->getXpath('frontend/routers/' . $urlParts[0]);
            if ($module) {
                $module = (string)$module[0]->args->module;
            }*/
            $module = $this->request->getControllerModule();
            //M1 > M2 Translation End
        }
        
        return array(
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'route'=> $route
        );
    }

    /**
     * Checks whether the given url is protected for suppliers
     *
     * @param string $url
     *
     * @return boolean
     */
    private function isSupplierProtectedUrl($url)
    {
        $isProtected = false;
        if (strpos($url, 'http') === 0) {
            foreach ($this->supplierProtectedLinks as $link) {
                //M1 > M2 Translation Begin (Rule p2-4)
                //if ($url == Mage::getUrl($link)) {
                if ($url == $this->_getUrl($link)) {
                //M1 > M2 Translation End
                    $isProtected = true;
                    continue;
                }
            }
        } else {
            $isProtected = in_array($url, $this->supplierProtectedLinks);
        }

        return $isProtected;
    }

    /**
     * @param $module
     * @return bool
     */
    private function isAccessRightsActive()
    {
        return (bool) $this->scopeConfig->isSetFlag('epicor_common/accessrights/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $module
     * @return bool
     */
    private function canAccess($module)
    {
        return $this->isAccessRightsActive()
            && (strpos($module, 'Epicor_') !== false || strpos($module, 'Mage_') !== false);
    }

    /**
     * Can show AR Payments tab?
     * @param \Epicor\Comm\Model\Customer $customer
     * @return bool
     */
    public function isCentralCollection($customer)
    {
        if (($customer->isSalesRep() && !$this->commHelper->isMasquerading()) || $customer->isSupplier() || $customer->isGuest()) {
            return false;
        }
        $erpAccount = $this->commHelper->getErpAccountInfo();
        if ($erpAccount->isTypeB2b() || $customer->isDealer()) {
            $isCentralCollection = $erpAccount->getIsCentralCollection();
            return $isCentralCollection ? false : true;
        }
        return false;
    }
}
