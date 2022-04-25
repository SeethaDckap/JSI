<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class Listing extends \Epicor\Comm\Controller\Returns
{



    public function execute()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $this->loadLayout()->renderLayout();
    }

    }
