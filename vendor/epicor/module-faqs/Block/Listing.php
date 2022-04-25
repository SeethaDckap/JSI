<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block;


/**
 * Front-end Faqs List block
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @property Epicor_Faqs_Model_Resource_Faqs_Collection $_faqsCollection
 * 
 */
class Listing extends \Magento\Framework\View\Element\Template
{

    /**
     * Container for the fetched FAQs
     *
     * @var \Epicor\Faqs\Model\ResourceModel\Faqs\Collection
     */
    protected $_faqsCollection = null;

    /**
     * @var \Epicor\Faqs\Model\ResourceModel\Faqs\CollectionFactory
     */
    protected $faqsResourceFaqsCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Faqs\Helper\Data
     */
    protected $faqsHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Faqs\Model\ResourceModel\Faqs\CollectionFactory $faqsResourceFaqsCollectionFactory,
        \Epicor\Faqs\Helper\Data $faqsHelper,
        \Magento\Customer\Model\Session $customerSession,       
        array $data = []
    ) {
        $this->faqsResourceFaqsCollectionFactory = $faqsResourceFaqsCollectionFactory;
        $this->storeManager = $context->getStoreManager();
        $this->scopeConfig=$context->getScopeConfig();
        $this->faqsHelper = $faqsHelper;
        $this->customerSession = $customerSession;
        
        //add accordion js file if required 
        if($this->scopeConfig->isSetFlag('faqs/view/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->getValue('faqs/view/presentation',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'accordion'){
            $context->getPageConfig()->addPageAsset('Epicor_Faqs::epicor/faqs/js/createAccordion.js');
        }
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Retrieve faqs collection
     *
     * @return \Epicor\Faqs\Model\ResourceModel\Faqs\Collection
     */
    protected function _getCollection()
    {
        return $this->faqsResourceFaqsCollectionFactory->create();
    }

    /**
     * Retrieve prepared faqs collection, filtered by stores and sorted by weight
     *
     * @return \Epicor\Faqs\Model\ResourceModel\Faqs\Collection
     */
    public function getCollection()
    {
        //We fetch only the F.A.Q. active for the current store.
        $currentStore = $this->storeManager->getStore()->getId();
        if (is_null($this->_faqsCollection)) {
            $this->_faqsCollection = $this->_getCollection()
                ->addFieldToFilter('stores', array('finset' => $currentStore));
            if ($this->faqsHelper->getSortParameter() == 'usefulness') {
                $this->_faqsCollection->addExpressionFieldToSelect('usefulness', '(useful-useless)', array('useful' => 'useful', 'useless' => 'useless'))
                    ->addOrder('usefulness', 'DSC');
            } else {
                $this->_faqsCollection->addOrder('weight', 'ASC');
            }
        }

        return $this->_faqsCollection;
    }

    public function isIndexedView()
    {
        return $this->faqsHelper->getPresentation() == 'paragraphs';
    }

    public function isCustomerRegistered()
    {
        $customerSession = $this->customerSession;
        return $customerSession->isLoggedIn();
    }
//M1 > M2 Translation Begin (Rule p2-5.13)
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }
    //M1 > M2 Translation End
}
