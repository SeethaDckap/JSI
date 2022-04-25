<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Helper;


/**
 * Faqs Data helper
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @property Epicor_Faqs_Model_Faqs $_faqsItemInstance
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Path to store config if front-end output is enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'faqs/view/enabled';

    /**
     * Path to store config that defines the F.A.Q.s sorting parameter
     *
     * @var string
     */
    const XML_PATH_SORT = 'faqs/view/sort';

    /**
     * Path to store config that defines the front-end presentation of F.A.Q.s 
     *
     * @var string
     */
    const XML_PATH_PRESENTATION = 'faqs/view/presentation';

    /**
     * Faqs Item instance for lazy loading
     *
     * @var \Epicor\Faqs\Model\Faqs
     */
    protected $_faqsItemInstance;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context    
    ) {
        $this->registry = $registry;
        $this->backendAuthSession = $backendAuthSession;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
    }
    /**
     * Checks whether faqs can be displayed in the frontend
     *
     * @param integer|string|\Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Checks the current active presentation in the store's configuration
     * @param integer|string|\Magento\Store\Model\Store $store
     * @return string
     */
    public function getPresentation($store = null)
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_PRESENTATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Return current faqs item instance from the Registry
     *
     * @return \Epicor\Faqs\Model\Faqs
     */
    public function getFaqsItemInstance()
    {
        if (!$this->_faqsItemInstance) {
            $this->_faqsItemInstance = $this->registry->registry('faqs_item');

            if (!$this->_faqsItemInstance) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Faqs item instance does not exist in Registry'));
            }
        }

        return $this->_faqsItemInstance;
    }

    /**
     * Return wether the current user is allowed to perform the requested action
     *
     * @return boolean
     */
    public function isActionAllowed($action)
    {
        return $this->backendAuthSession->isAllowed('faqs/manage/' . $action);
    }

    /**
     * Return the field by which the F.A.Q. are to be oredered
     *
     * @return string ('weight'|'usefulness')
     */
    public function getSortParameter($store = null)
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_SORT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Return the the current user ID
     * 
     * @return int
     */
    public function getUserId()
    {
        return (int) $this->customerSession->getId();
    }

}
