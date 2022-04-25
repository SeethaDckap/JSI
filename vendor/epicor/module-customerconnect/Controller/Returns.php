<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller;


/**
 * Returns controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Returns extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->generic = $generic;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    public function preDispatch()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if (!$helper->isReturnsEnabled()) {
            $this->messageManager->addErrorMessage(__('Returns not available'));
            $this->_redirect('/');
        }
    }
}
