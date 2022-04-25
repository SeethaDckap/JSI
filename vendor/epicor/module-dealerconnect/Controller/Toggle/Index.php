<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Toggle;

class Index extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $registry;
        
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->registry = $registry;
        parent::__construct(
            $context
        );
    }


    public function execute() {

        $currentMode = $this->customerSession->getDealerCurrentMode() === "shopper" ? "dealer" : "shopper";
        $this->customerSession->setDealerCurrentMode($currentMode);
        $result = array(
            'type' => 'success',
            'mode' => $currentMode
        );
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

}
