<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class QuoteConfirm extends \Epicor\Customerconnect\Controller\Rfqs\Confirm
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_claim_confirmrejects';

    public function execute() {
        $data = $this->getRequest()->getPost();
        unset($data['claim_address']);
        unset($data['case_number']);
        unset($data['claim_comment']);
        unset($data['old_claim_data']);

        $response = json_encode(array('message' => __('No Data Sent'), 'type' => 'error'));
        $this->getResponse()->setBody($response);
        if ($data) {
            $helper = $this->customerconnectRfqHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Rfq */

            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */

            $data = $commHelper->sanitizeData($data);

            $response = $helper->processRfqCrqc('confirm', $data, $response);
        }

        $this->getResponse()->setBody($response);
    }

}
