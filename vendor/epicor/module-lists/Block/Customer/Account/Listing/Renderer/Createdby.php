<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


/**
 * List Grid ownerid grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Createdby extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $value = $row->getData($this->getColumn()->getIndex());
        if (!empty($value)) {
            $Customer = $this->customerCustomerFactory->create();
            $getCreatedBy = $Customer->load($value);
            $emailId = $getCreatedBy->getEmail();
            $html = $emailId;
        }
        return $html;
    }

}
