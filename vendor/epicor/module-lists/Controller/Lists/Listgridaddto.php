<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class Listgridaddto extends \Epicor\Customerconnect\Controller\Generic
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
        \Magento\Framework\View\LayoutFactory $layoutFactory
    )
    {
        $this->layoutFactory = $layoutFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * List ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $output = $this->getLayoutFactory()->create()->createBlock('Epicor\Lists\Block\Customer\Account\Listing\AddtoGrid')->toHtml();
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
