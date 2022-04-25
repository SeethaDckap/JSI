<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Controller\Access\Management\Generic;

class Index extends \Epicor\Common\Controller\Access\Management\Generic
{

    public function execute()
    {
        $this->loadErpAccount();
        $this->loadLayout()->renderLayout();
    }

    }
