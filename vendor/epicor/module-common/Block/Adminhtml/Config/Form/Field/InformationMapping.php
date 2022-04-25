<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field;


class InformationMapping extends \Magento\Framework\View\Element\Html\Select
{

    protected $gridMappingHelper;

    protected $messageTypes;

    protected $paramsOptions = false;

    protected $messageSection = "information_section";

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
            $paramsOptions = $this->paramsOptions;
            $optionVals ='';
            if($paramsOptions) {
                $optionVals = $paramsOptions;
            }
            $options = $this->gridMappingHelper->getInformationMappingValues($messageTypes,$this->messageSection);
            if (!empty($options)) {
                foreach ($options as $key => $vals) {
                    $this->addOption($key, __($vals),$optionVals);
                }
            }
        }
        return parent::_toHtml();
    }

}
