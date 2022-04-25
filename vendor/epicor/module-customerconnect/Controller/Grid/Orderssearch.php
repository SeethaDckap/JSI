<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Grid;

class Orderssearch extends \Epicor\Customerconnect\Controller\Grid
{
    /**
     * Execute controller.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $result = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $result->getLayout()
                ->createBlock('Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Grid')
                ->toHtml()
        );
    }

}
