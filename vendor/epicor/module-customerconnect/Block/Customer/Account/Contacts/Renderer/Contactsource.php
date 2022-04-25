<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer;


class Contactsource extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->customerconnectHelper;
        $id = $row->getSource();
        switch ($id) {
            case $helper::SYNC_OPTION_ONLY_ERP:
                $value = __("ERP only");
                break;
            case $helper::SYNC_OPTION_ONLY_ECC:
                $value = __("Web only");
                break;
            case $helper::SYNC_OPTION_ECC_ERP:
                $value = __("Both");
                break;
        }
        return $value;
    }

}

?>