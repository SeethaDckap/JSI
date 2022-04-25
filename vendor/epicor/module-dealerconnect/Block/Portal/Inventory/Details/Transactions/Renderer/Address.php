<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details\Transactions\Renderer;


/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }    

    public function render(\Magento\Framework\DataObject $row)
    {
        $addressFields = array('name', 'address1', 'address2', 'address3', 'city', 'county', 'country', 'postcode','telephone_mumber','fax_number');
        $glue = '';
        $text = '';
        foreach ($addressFields as $field) {
            $fieldData = trim($row->getData($field));
            if ($fieldData && !empty($fieldData)) {
                $text .= $glue . $fieldData;
                $glue = ', ';
            }
        }
        
        $text .= "<br>Email Id:". $row->getData("email_address");
        $text .= "<br>Comment:". $row->getData("tran_comment");

        return $text;
    }

}
