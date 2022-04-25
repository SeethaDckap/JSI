<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class Selector extends \Epicor\Lists\Controller\Lists
{

    /**
     * Sends the Lists grid Selector
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }

}
