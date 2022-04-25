<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Observer
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Observer\Ast;

use Epicor\Comm\Model\Message\Request\AstFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;

/**
 * Class AlterMethodCode
 */
class Send implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Customer session model.
     *
     * @var Session
     */
    private $customerSession;

    /**
     * Ast message factory.
     *
     * @var AstFactory
     */
    private $commMessageRequestAstFactory;

    /**
     * Registry.
     *
     * @var Registry
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param Session    $customerSession              Customer session model.
     * @param AstFactory $commMessageRequestAstFactory Ast message factory.
     * @param Registry   $registry                     Registry.
     */
    public function __construct(
        Session $customerSession,
        AstFactory $commMessageRequestAstFactory,
        Registry $registry
    ) {
        $this->customerSession              = $customerSession;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->registry = $registry;

    }//end __construct()


    /**
     * Execute function.
     *
     * @param Observer $observer Event observer.
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $ast     = $this->commMessageRequestAstFactory->create();
            $sendAst = true;

            if (!$this->registry->registry('SkipEvent') && $ast->isActive('ast_at_login')
                && !$customer->isSupplier() && $sendAst && $this->customerSession->getIsPunchout()) {
                $ast->setCustomer($customer);
                $ast->sendMessage();
            }
        }

    }//end execute()


}//end class
