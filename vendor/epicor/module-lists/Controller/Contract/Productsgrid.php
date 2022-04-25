<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Contract;

class Productsgrid extends \Epicor\Lists\Controller\Lists {

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
    )
    {
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
     * Select Contract ajax reload of grid tab
     *
     * @return void
     */
    public function execute() {

        $output = $this->getLayoutFactory()->create()->createBlock('Epicor\Lists\Block\Contract\Products\Grid')->toHtml();
        $this->getResponse()->appendBody($output);
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory() {
        return $this->layoutFactory;
    }

}
