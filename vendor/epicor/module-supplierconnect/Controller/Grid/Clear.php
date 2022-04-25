<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Grid;

class Clear extends \Epicor\Supplierconnect\Controller\Grid
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Url\Decoder $urlDecoder
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->urlDecoder = $urlDecoder;
        $this->cache = $cache;
        $this->urlDecoder = $urlDecoder;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Clea action - clears the cache for the specified grid
     */
    public function execute()
    {

        $customerId = $this->customerSession->getCustomer()->getId();

        $grid = $this->request->getParam('grid');
        $location = $this->urlDecoder->decode($this->request->getParam('location'));

        $tags = array('CUSTOMER_' . $customerId . '_SUPPLIERCONNECT_' . strtoupper($grid));
        $cache = $this->cache;
        $cache->clean($tags);
        $this->_redirect($location);
    }

}
