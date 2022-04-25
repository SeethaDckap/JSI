<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Removedeliveryaddress extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $listsFrontendHelper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Epicor\Lists\Helper\Frontend $listsFrontendHelper
    ) {
        
        $this->listsFrontendHelper = $listsFrontendHelper;
        
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $backendJsHelper,
            $commHelper,
            $listsListModelFactory,
            $generic,
            $listsHelper,
            $listsFrontendRestrictedHelper,
            $timezone
        );
    }
    
    /**
     * Remove delivery address action in Grid
     * return null
     */
    public function execute()
    {
        $helper = $this->listsFrontendHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend */
        $helper->setSelectedAddress(null);
        $this->_redirect('*/*/deliveryaddress');
    }

}
