<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller;


/**
 * Contract frontend actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
abstract class Contract extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * Contract list page
     *
     * @return void
     */
//    public function indexAction()
//    {
//        $contractHelper = Mage::helper('epicor_lists/frontend_contract');
//        if ($contractHelper->contractsDisabled()) {
//            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl());
//            return;
//        }
//        $this->loadLayout();
//        $this->renderLayout();
//    }


    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory
    )
    {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->listsListModelFactory = $listsListModelFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($contractHelper->listsDisabled()) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
            //M1 > M2 Translation Begin (Rule p2-6.2)
            //Mage::app()->getFrontController()->getResponse()->setRedirect($this->_url->getUrl('customer/account'));
            $this->_redirect($this->_url->getUrl('customer/account'));
            //M1 > M2 Translation End
            //M1 > M2 Translation End
        }
    }

    /**
     * Loads List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function loadEntity()
    {
        $id = $this->getRequest()->getParam('id', null);
        $list = $this->listsListModelFactory->create()->load($id);
        return $list;
    }
}
