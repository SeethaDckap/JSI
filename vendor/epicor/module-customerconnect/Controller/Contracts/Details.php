<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Contracts;

class Details extends \Epicor\Customerconnect\Controller\Contracts {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Model\Message\Request\Cccs
     */
    protected $listsMessageRequestCccs;

    /**
     * @var \Epicor\Lists\Model\Message\Request\Cccs
     */
    protected $listsMessageRequestCccd;

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
    \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Epicor\Customerconnect\Model\Message\Request\Cccs $listsMessageRequestCccs, \Epicor\Lists\Model\Message\Request\Cccd $listsMessageRequestCccd, \Epicor\Common\Helper\Access $commonAccessHelper, \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Registry $registry, \Magento\Framework\Session\Generic $generic, \Magento\Framework\Url\DecoderInterface $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsMessageRequestCccs = $listsMessageRequestCccs;
        $this->listsMessageRequestCccd = $listsMessageRequestCccd;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->generic = $generic;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $customerconnectHelper, $request, $listsMessageRequestCccs, $registry, $generic, $urlDecoder, $encryptor
        );
    }

    /**
     * Detail action - show contract details 
     */
    public function execute() {
        $contractCode = $this->request->getParam('contract');
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        $erpAccountNumber = $helper->getErpAccountNumber();
        $contractInfo = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($contractCode)));
        if (
                count($contractInfo) == 2 &&
                $contractInfo[0] == $erpAccountNumber &&
                !empty($contractInfo[1])
        ) {

            $message = $this->listsMessageRequestCccd;
            $error = false;
            $messageTypeCheck = $message->getHelper("epicor_list/messaging")->getMessageType('CCCD');
            if ($message->isActive() && $messageTypeCheck) {
                //M1 > M2 Translation Begin (Rule p2-6.4)
                /* $message->setAccountNumber($erpAccountNumber)
                  ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()))
                  ->setContractCode($contractInfo[1]); */
                $message->setAccountNumber($erpAccountNumber)
                        ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()))
                        ->setContractCode($contractInfo[1]);
                //M1 > M2 Translation End


                if ($message->sendMessage()) {
                    $this->registry->register('epicor_lists_contracts_details', $message->getResponse());

                    $accessHelper = $this->commonAccessHelper;
                    /* @var $helper Epicor_Common_Helper_Access */
                    $this->registry->register('manage_permissions', $accessHelper->customerHasAccess('Epicor_Lists', 'Contract', 'index', 'manage_permissions', 'view'));
                } else {
                    $error = true;
                    $this->generic->addError(__('Failed to retrieve Customer Contract Details'));
                }
            } else {
                $error = true;
                $this->generic->addError(__('Customer Contract Details not available'));
            }
        } else {
            $error = true;
            $this->generic->addError(__('Customer Contract Id not supplied, cannot display details'));
        }
        return $this->resultPageFactory->create();
    }

}
