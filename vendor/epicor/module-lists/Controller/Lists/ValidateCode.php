<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class ValidateCode extends \Epicor\Lists\Controller\Lists
{

    public function execute()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */
        $this->getResponse()->setBody(json_encode($helper->validateNewListCode($this->getRequest())));
    }

}
