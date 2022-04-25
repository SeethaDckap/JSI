<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field;


class AddressMapping extends \Magento\Framework\View\Element\Html\Select
{

    protected $gridMappingHelper;

    protected $messageTypes;


    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    ) {
        $this->gridMappingHelper = $gridMappingHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function _toHtml()
    {
        $messageTypes = $this->messageTypes;
        if($messageTypes) {
            $messageSection ="address_section";
            $options = $this->gridMappingHelper->getMappingValues($messageTypes,$messageSection);
            if (!empty($options)) {
                foreach ($options as $key => $vals) {
                    $this->addOption($key, __($vals));
                }
            }
        }
        return parent::_toHtml();
    }

}
