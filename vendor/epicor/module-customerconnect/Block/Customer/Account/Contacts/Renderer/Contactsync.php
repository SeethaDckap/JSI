<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Contacts\Renderer;


class Contactsync extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
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
        $source = $row->getSource();
        $out = '<select class="action-select accountselect" onchange="varienGridAction.execute(this);">'
            . '<option value=""></option>';
        foreach ($actions as $action) {
            if (is_array($action)) {
                $syncAction = __('Sync Contact');
                if ((($action['caption'] == $syncAction && $source == $helper::SYNC_OPTION_ONLY_ECC) || $action['caption'] != $syncAction)) {
                    $out .= $this->_toOptionHtml($action, $row);
                }
            }
        }
        $out .= '</select>';
        return $out;
    }

}

?>
