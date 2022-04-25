<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Customer\Erpaccount;


/**
 * Locations Manufacturers renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        array $data = []
    ) {
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $addressCode = $row->getData($this->getColumn()->getIndex());

        $addressCollection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        $addressCollection->addFieldToFilter('erp_code', $addressCode);

        $address = $addressCollection->getFirstItem();

        $addressFields = array('name', 'address1', 'address2', 'address3', 'city', 'county', 'country', 'postcode');

        $glue = '';
        if ($address) {
            foreach ($addressFields as $field) {
                $fieldData = trim($address->getData($field));
                if ($fieldData && !empty($fieldData)) {
                    $html .= $glue . $fieldData;
                    $glue = ', ';
                }
            }
        }

        return $html;
    }

}
