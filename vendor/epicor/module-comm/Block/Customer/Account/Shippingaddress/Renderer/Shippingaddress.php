<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Account\Shippingaddress\Renderer;
use Magento\Customer\Model\Session;


class Shippingaddress extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    protected $_customerSession;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        Session $customerSessionFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSessionFactory;
        parent::__construct(
            $context,
            $data
        );
    }  
    
    //   protected $updateList = Array();
    public function render(\Magento\Framework\DataObject $row)
    {
        
        $customer = $this->_customerSession->getCustomer();
        $customerId = $customer->getId();
        $datas = $row->getData();
        $addressD1 = $datas['street'];
        $address = array($addressD1);
        $datas['street'] = $address;
        $datas['customer_id'] = $customerId;
        $jsonArray = json_encode($datas);
        $html = '<input type="text" class="details" name="details"';
        $html .= '" style="display:none" value="' . htmlspecialchars($jsonArray) . '"/> ';
        $html .= $row->getId();
        return $html;
    }

}
