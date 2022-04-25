<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller;


/**
 * Invoices controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Invoices extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuid
     */
    protected $customerconnectMessageRequestCuid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cuid $customerconnectMessageRequestCuid,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->request = $request;
        $this->customerconnectMessageRequestCuid = $customerconnectMessageRequestCuid;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    protected function _loadInvoice()
    {
        $loaded = false;
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $invoice = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($this->request->getParam('invoice'))));

        if (
            count($invoice) == 2 &&
            $invoice[0] == $erpAccountNumber &&
            !empty($invoice[1])
        ) {
            $cuid = $this->customerconnectMessageRequestCuid;
            $messageTypeCheck = $cuid->getHelper()->getMessageType('CUID');

            if ($cuid->isActive() && $messageTypeCheck) {

                //M1 > M2 Translation Begin (Rule p2-6.4)
                /*$cuid->setAccountNumber($erpAccountNumber)
                    ->setInvoiceNumber($invoice[1])
                    ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()))
                    ->setType($this->getRequest()->getParam('attribute_type'));*/
                $cuid->setAccountNumber($erpAccountNumber)
                    ->setInvoiceNumber($invoice[1])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()))
                    ->setType($this->getRequest()->getParam('attribute_type'));
                //M1 > M2 Translation End


                if ($cuid->sendMessage()) {
                    $this->registry->register('customer_connect_invoices_details', $cuid->getResults());
                    $loaded = true;
                } else {
                    $this->messageManager->addErrorMessage(__("Failed to retrieve Invoice Details"));
                }
            } else {
                $this->messageManager->addErrorMessage(__("ERROR - Invoice Details not available"));
            }
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Invalid Invoice Number"));
        }

        return $loaded;
    }

}
