<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Password;

class Index extends \Epicor\Supplierconnect\Controller\Password
{

    public function __construct(

    ) {
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $this->loadLayout()->renderLayout();
    }

    }
