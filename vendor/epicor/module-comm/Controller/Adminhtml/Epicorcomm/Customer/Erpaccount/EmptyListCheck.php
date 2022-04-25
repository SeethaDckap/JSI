<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class EmptyListCheck extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    /**
     * Adding a warning for exclude-unchecked and empty set.
     */
    public function execute()
    {
        $data = $this->request->getParams();
        $response = array(
            'message' => '',
            'exclusionerror' => false,
        );

        if (isset($data['id']) && isset($data['links'])) {
            $model = $this->commCustomerErpaccountFactory->create()->load($data['id']);

            if ($model->getId()) {
                $delivery = array_keys($this->commonHelper->decodeGridSerializedInput($data['links']['delivery']));
                $payments = array_keys($this->commonHelper->decodeGridSerializedInput($data['links']['payments']));

                if (!isset($data['exclude_selected_delivery'])) {
                    if ((isset($data['links']['delivery'])) && empty($delivery) && ($model->getAllowedDeliveryMethods() !== 'a:0:{}')) {
                        $response['message'] = "No Delivery Methods have been selected to Include. One or more Delivery Methods should be chosen if 'Exclude selected Delivery Methods' is not ticked
.\n";
                        $response['exclusionerror'] = true;
                    }
                }
                if (!isset($data['exclude_selected_payments'])) {
                    if ((isset($data['links']['payments'])) && empty($payments) && ($model->getAllowedPaymentMethods() !== 'a:0:{}')) {
                        $response['message'] = $response['message'] . "No Payment Methods have been selected to Include. One or more Payment Methods should be chosen if 'Exclude selected payments' is not ticked
";
                        $response['exclusionerror'] = true;
                    }
                }
            }
        }
        $this->response->setBody(json_encode($response));
    }

}
