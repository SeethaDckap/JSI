<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Gor;

class SetContact extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $gor = $observer->getEvent()->getMessage();
        /* @var $gor Epicor_Comm_Model_Message_Upload_Gor */
        $order = $gor->getOrder();
        /* @var $order Epicor_Comm_Model_Order */
        $xml = $gor->getMessageArray();

        if ($order->getEccSalesrepCustomerId()) {
            $contact = unserialize($order->getEccSalesrepChosenCustomerInfo());

            if (isset($contact['id'])) {
                unset($contact['id']);
            }

            if (empty($contact)) {
                $xml['messages']['request']['body']['contact'] = array(
                    'contactCode' => '',
                    'name' => $order->getCustomerName(),
                    'function' => null,
                    'telephoneNumber' => null,
                    'faxNumber' => null,
                    'emailAddress' => $order->getCustomerEmail(),
                    'eccLoginId' => $order->getCustomerId(),
                );
            } else {
                $xml['messages']['request']['body']['contact'] = array(
                    'contactCode' => $contact['contact_code'],
                    'name' => $contact['name'],
                    'function' => $contact['function'],
                    'telephoneNumber' => $contact['telephone_number'],
                    'faxNumber' => $contact['fax_number'],
                    'emailAddress' => $contact['email'],
                    'eccLoginId' => $contact['ecc_login_id'],
                );
            }

            $gor->setMessageArray($xml);
        }
    }

}