<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller;


/**
 * Supplierconnect abstract controller
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
abstract class Generic extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Epicor\SalesRep\Controller\Context $context
    )
    {
        $this->customerSession = $context->getCustomerSession();
        parent::__construct(
            $context
        );
    }


    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    /* public function preDispatch()
     {
         // a brute-force protection here would be nice

         parent::preDispatch();

         if (!$this->customerSession->authenticate($this)) {
             $this->setFlag('', 'no-dispatch', true);
         }
     }*/

    /*public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {

        if (!$this->customerSession->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }*/

}
