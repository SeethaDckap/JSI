<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Customer\Account;

class Login extends \Epicor\B2b\Controller\Customer\Account
{

    /**
     * Customer login form page
     */
    public function execute()
    {
        if ($this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            // $this->_redirect('b2b/portal/login');
            $this->_forward('login', 'portal', 'b2b');
        } else {
            if ($this->getRequest()->getParam('access') != 'denied') {
                parent::execute();
            }
        }
    }

}
