<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class Index extends \Epicor\Comm\Controller\Onepage
{

    public function __construct(

    ) {
    }
    /**
     * Shipping method save action
     */
    public function execute()
    {
        if ($this->getRequest()->get('grid')) {

            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('epicor_comm/customer_account_billingaddress_listing')->toHtml()
            );
        }
        parent::indexAction();
    }

    }
