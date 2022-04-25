<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class NewAction extends \Epicor\AccessRight\Controller\Action
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_inventory_create';
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Deiu
     */
    protected $dealerconnectMessageRequestDeiu;
    
    
    protected $resultPageFactory;
    
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    protected $urlDecoder;

    protected $encryptor;


    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;    
    
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;    

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deiu $dealerconnectMessageRequestDeiu
    )
    {
        $this->_localeResolver = $localeResolver;
        $this->dealerconnectMessageRequestDeiu = $dealerconnectMessageRequestDeiu;
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;        
        parent::__construct(
            $context
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        //$helper = $this->customerconnectHelper;
        $deiu = $this->dealerconnectMessageRequestDeiu;
        $messageTypeCheck = $deiu->getHelper()->getMessageType('DEIU');
        
        if ($deiu->isActive() && $messageTypeCheck) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            $pageMainTitle->setPageTitle(__('New Inventory'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('Account update not available'));
        }
        return array('redirect' => $this->_url->getUrl('dealerconnect/inventory/search'), 'type' => 'success');
    }
}
