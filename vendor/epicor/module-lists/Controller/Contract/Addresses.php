<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class Addresses extends \Epicor\Lists\Controller\Contract
{

    public function __construct(

    ) {
    }
    /**
     * Addresses initial grid tab load
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();
        $this->loadLayout();
        $this->getLayout()->getBlock('contract_addresses')
            ->setSelected($this->getRequest()->getPost('addresses', null));
        $this->renderLayout();
    }

    }
