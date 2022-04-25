<?php

namespace Silk\CustomAccount\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Epicor\Customerconnect\Controller\Account
{
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper
    )
    {
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }


    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->isDispatched() && $request->getActionName() !== 'denied' &&
            ( !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_aged_balances_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_shipping_details_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_contacts_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_period_balances_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_information_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_billing_read'
                )

            )
        ) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;

        }

        return parent::dispatch($request);
    }
    /**
     * Index action
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $message = $this->customerconnectMessageRequestCuad;
        $error = false;
        $messageTypeCheck = $message->getHelper()->getMessageType('CUAD');
        if ($message->isActive() && $messageTypeCheck) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            /*$message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));*/
            $message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            //M1 > M2 Translation End


            if ($message->sendMessage()) {
                $this->registry->register('customer_connect_account_details', $message->getResults());

                $accessHelper = $this->commonAccessHelper;
                $this->registry->register('manage_permissions', $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Account', 'index', 'manage_permissions', 'view'));
            } else {
                $error = true;
                $this->messageManager->addErrorMessage(__('Failed to retrieve Account Details'));
            }
        } else {
            $error = true;
            $this->messageManager->addErrorMessage(__('Account Details not available'));
        }

        $result = $this->resultPageFactory->create();

        $pageMainTitle = $result->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
            $pageMainTitle->setPageTitle('Account Information');
        }
        
        
        return $result;
    }
}
