<?php
namespace Silk\CustomAccount\Model\Config\Source;

class UpperChargeType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'fixed', 'label' => __('Fixed Value')],
            ['value' => 'percent', 'label' => __('Percent')],
        ];
    }
}
?>