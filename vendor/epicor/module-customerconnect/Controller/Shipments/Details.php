<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Shipments;

class Details extends \Epicor\Customerconnect\Controller\Shipments
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_shipments_details';

    public function execute()
    {
        if ($this->_loadShipment()) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $shipment = $this->registry->registry('customer_connect_shipments_details');
                $pageMainTitle->setPageTitle(__('Pack Slip : %1', $shipment->getPackingSlip()));
            }
            return $resultPage;
        }
            
        if ($this->messageManager->getMessages()->getItems()) {
            session_write_close();
            $this->_redirect('*/shipments/index');
        }
    }

}
