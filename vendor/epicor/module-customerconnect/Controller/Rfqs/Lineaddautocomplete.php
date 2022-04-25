<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Lineaddautocomplete extends \Epicor\Customerconnect\Controller\Rfqs
{

    public function execute()
    {
        $layout = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody($layout->getLayout()->createBlock('Epicor\Comm\Block\Cart\Quickadd\Autocomplete')->toHtml());
    }

}
