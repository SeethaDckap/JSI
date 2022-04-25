<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Grid;

class Clear extends \Magento\Framework\App\Action\Action
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
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->request = $request;
        $this->cache = $cache;
        $this->urlDecoder = $urlDecoder;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
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

        $tags = array('CUSTOMER_' . $customerId . '_DEALERCONNECT_' . strtoupper($grid));

        //M1 > M2 Translation Begin (Rule p2-6.7)
        //$cache = Mage::app()->getCacheInstance();
        $cache = $this->cache;
        //M1 > M2 Translation End
        $cache->clean($tags);

        $this->_redirect($location);
    }

}
