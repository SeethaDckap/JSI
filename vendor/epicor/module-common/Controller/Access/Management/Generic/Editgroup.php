<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Controller\Access\Management\Generic;

class Editgroup extends \Epicor\Common\Controller\Access\Management\Generic
{


public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $this->_loadGroup($id);
        $this->loadLayout()->renderLayout();
    }

    }
