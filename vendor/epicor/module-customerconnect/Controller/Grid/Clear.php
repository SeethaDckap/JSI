<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Grid;

class Clear extends \Epicor\Customerconnect\Controller\Grid
{

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
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Url\Decoder $urlDecoder
    )
    {
        $this->request = $request;
        $this->cache = $cache;
        $this->urlDecoder = $urlDecoder;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectHelper,
            $customerconnectMessageRequestCuad,
            $registry,
            $commonAccessHelper,
            $generic
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

        $tags = array('CUSTOMER_' . $customerId . '_CUSTOMERCONNECT_' . strtoupper($grid));

        //M1 > M2 Translation Begin (Rule p2-6.7)
        //$cache = Mage::app()->getCacheInstance();
        $cache = $this->cache;
        //M1 > M2 Translation End
        $cache->clean($tags);

        $this->_redirect($location);
    }

}
