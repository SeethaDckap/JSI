<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller;


/**
 * Grid controller, handles generic gird functions
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Grid extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

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
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->generic = $generic;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    protected function recreateCUAD()
    {

        //resend cuad as registry entry is empty after display. This is copied from the Epicor_Customerconnect_AccountController index (except for last bit) 
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
    }

}
