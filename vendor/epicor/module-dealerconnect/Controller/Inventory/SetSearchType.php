<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class SetSearchType extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var \\Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper
    )
    {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * Index action
     */
    public function execute()
    {
        $filterAllow = $this->_request->getParam('filter');
        $helper = $this->dealerHelper;
        if(!$filterAllow){
            $searchFilterType = $helper->checkCusInventorySearch();
            if($searchFilterType == 1){
                $this->customerSession->setDeisFilterType('all');
            }elseif ($searchFilterType == 2){
                $dealerGrp = $helper->getDealerGroup();
                $this->customerSession->setDeisFilterType('dealergroup');
                $this->customerSession->setDealerGrpType("$dealerGrp");
            }
        }else{
            $this->customerSession->setDeisFilterType('own');
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(array('success'=>true));
        return $resultJson;
    }

}
