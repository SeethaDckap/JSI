<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Listgrid extends \Epicor\Lists\Controller\Lists
{ 
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

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
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        
        $this->layoutFactory = $layoutFactory;
        
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
     * List ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        //$this->loadLayout();
        //$this->getResponse()->setBody($this->getLayout()->createBlock('epicor_lists/customer_account_list_grid')->toHtml());
        
        $output = $this->getLayoutFactory()->create()->createBlock('Epicor\Lists\Block\Customer\Account\Listing\Grid')->toHtml();
        $this->getResponse()->appendBody($output);

    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }
}
