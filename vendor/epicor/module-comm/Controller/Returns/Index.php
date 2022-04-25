<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class Index extends \Epicor\Comm\Controller\Returns
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_returns_create';

    const FRONTEND_RESOURCE_EDIT = 'Epicor_Customerconnect::customerconnect_account_returns_edit';

    const FRONTEND_DEALERCONNECT_RESOURCE = 'Dealer_Connect::dealer_orders_return';

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry);
    }


    public function execute()
    {
        $session = $this->customerSession;
        /* @var $session Mage_Customer_Model_Session */

        $customerName = $session->getReturnGuestName();
        $customerEmail = $session->getReturnGuestEmail();
        $session->unsReturnGuestName();
        $session->unsReturnGuestEmail();


        if(!$this->customerSession->isLoggedIn()) {
            $session->setReturnGuestName($customerName);
            $session->setReturnGuestEmail($customerEmail);
        }

        $loadLayout = true;
        $returnId = $this->request->getParam('return');
        $erpReturn = $this->request->getParam('erpreturn');

        if (!empty($returnId)) {
            $return = $this->loadReturn($returnId, true);
            if (!$return) {
                $this->messageManager->addErrorMessage('Return not found');
                $this->_redirect('/');
                $loadLayout = false;
            }
        } else if (!empty($erpReturn)) {
            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */

            $return = $helper->loadErpReturn($helper->decodeReturn($erpReturn), null, true);

            if ($return) {
                $return = $this->loadReturn($return->getId(), false, $return);
            } else {
                $this->messageManager->addErrorMessage('Return not found');
                $this->_redirect('/');
                $loadLayout = false;
            }
        } else {
            if (!$this->customerSession->isLoggedIn()) {
                $this->messageManager->addErrorMessage('You do not have permission to access this return.');
                $this->_redirect('/');
                $loadLayout = false;
            }
        }

        if ($loadLayout) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->customerSession->setBeforeAuthUrl(Mage::getUrl('epicor_comm/returns/index'));
            $this->customerSession->setBeforeAuthUrl($this->_url->getUrl('epicor_comm/returns/index'));
            $page = $this->resultPageFactory->create();
			
            if ($this->customerSession->isLoggedIn()) {
                $page->addHandle('customer_account');
                $page->addHandle('customer_connect');
                $page->getConfig()->setPageLayout('2columns-left');
            } else {
                $page->getConfig()->setPageLayout('1column');
            }

            return $page;
            //M1 > M2 Translation End
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $referrerUrl = $this->_redirect->getRefererUrl();
        $code = static::FRONTEND_RESOURCE;
        $param = $this->getRequest()->getParam('return');
        switch(true) {
            case (strpos($referrerUrl, 'dealerconnect') && $param):
                $code = static::FRONTEND_DEALERCONNECT_RESOURCE;
                break;
            case (strpos($referrerUrl, 'customerconnect') && $param):
                $code = static::FRONTEND_RESOURCE_EDIT;
                break;
            case ($param):
                $code = static::FRONTEND_RESOURCE_EDIT;
                break;
            default:
                $code = static::FRONTEND_RESOURCE;
                break;
        }
        return $this->_isAccessAllowed($code);

    }
}
