<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer;


class Contactactions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->customerconnectHelper;
        $actions = $this->getColumn()->getActions();
        $id = $row->getSource();
        switch ($id) {
            case $helper::SYNC_OPTION_ONLY_ERP:
                unset($actions[2]);
                break;
        }
        //$actions = $this->getColumn()->getActions();
        return parent::render($row);
    }

}

?>