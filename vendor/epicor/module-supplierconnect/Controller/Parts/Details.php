<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Parts;

class Details extends \Epicor\Supplierconnect\Controller\Generic
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_parts_details';
    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spld
     */
    protected $supplierconnectMessageRequestSpld;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    protected $urlDecoder;
    
    protected $encryptor;

    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Supplierconnect\Model\Message\Request\Spld $supplierconnectMessageRequestSpld,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->request = $request;
        $this->generic = $generic;
        $this->commHelper = $commHelper;
        $this->supplierconnectMessageRequestSpld = $supplierconnectMessageRequestSpld;
        $this->registry = $registry;
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
    public function execute()
    {
        $helper = $this->supplierconnectHelper;
        $part_requested = unserialize($this->encryptor->decrypt($this->urlDecoder->decode($this->request->getParam('part'))));
        $erp_account_number = $this->commHelper->getSupplierAccountNumber();

        if (
            count($part_requested) == 5 &&
            $part_requested['erp_account'] == $erp_account_number &&
            !empty($part_requested['product_code'])
        ) {
            $spld = $this->supplierconnectMessageRequestSpld;
            $messageTypeCheck = $spld->getHelper("supplierconnect/messaging")->getMessageType('SPLD');

            if ($spld->isActive() && $messageTypeCheck) {

                $spld->setProductCode($part_requested['product_code'])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
                
                $spld->setOperationalCode($part_requested['operational_code']);
                $spld->setEffectiveDate($part_requested['effective_date']);
                $spld->setUnitOfMeasureCode($part_requested['unit_of_measure_code']);

                if ($spld->sendMessage()) {
                    $this->registry->register('supplier_connect_part_details', $spld->getResults());
                    return $this->resultPageFactory->create(); 
                } else {
                    $this->generic->addError('Failed to retrieve Part Details');
                }
            } else {
                $this->generic->addError('Part Details not available');
            }
        } else {
            $this->generic->addError('Invalid Part Number');
        }

        if ($this->generic->getMessages()->getItems()) {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

}
