<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


/**
 * Login redirect options for config
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
class Mappings
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'cardtype', 'label' => 'Card Type'),
            array('value' => 'country', 'label' => 'Country'),
            array('value' => 'currency', 'label' => 'Currency'),
            array('value' => 'customfields', 'label' => 'Custom Fields'),
            array('value' => 'erporderstatus', 'label' => 'Erp Order Status'),
            array('value' => 'erpquotestatus', 'label' => 'Erp Quote Status'),
            array('value' => 'Invoicestatus', 'label' => 'Invoice'),
            array('value' => 'language', 'label' => 'Language'),
            array('value' => 'orderstatus', 'label' => 'Order Status'),
            array('value' => 'payment', 'label' => 'Payment'),
            array('value' => 'reasoncode', 'label' => 'Reason Code'),
            array('value' => 'remotelinks', 'label' => 'Remote Links'),
            array('value' => 'rmastatus', 'label' => 'RMA'),
            array('value' => 'servicecallstatus', 'label' => 'Service Call'),
            array('value' => 'shippingmethods', 'label' => 'Shipping Method'),
            array('value' => 'erpattributes', 'label' => 'Erp Attributes'),
            array('value' => 'pac', 'label' => 'Pac Mapping'),
            array('value' => 'warranty', 'label' => 'Warranty Mapping'),
            array('value' => 'shippingstatus', 'label' => 'Ship Status'),
            array('value' => 'products', 'label' => 'Products Mapping'),
            array('value' => 'miscellaneouscharges', 'label' => 'Miscellaneous Code Mapping'),
            array('value' => 'claimstatus', 'label' => 'Claim Status'),
            array('value' => 'datamapping', 'label' => 'Data Mapping')
        );
    }

}
