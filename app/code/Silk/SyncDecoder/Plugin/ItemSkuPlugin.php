<?php
namespace Silk\SyncDecoder\Plugin;

class ItemSkuPlugin
{
    public function afterGetSku(
        \Magento\Quote\Model\Quote\Item $subject,
        $sku)
    {
        $additionalOptions = $subject->getBuyRequest()->getAdditionalOptions();
        if($additionalOptions && !empty($additionalOptions)){
            foreach ($additionalOptions as $additionalOption) {
                if(isset($additionalOption['option_sku']) && $additionalOption['option_sku']){
                    if($additionalOption['option_sku'] == 'A'){
                        $sku = 'A' . $sku;
                    }
                    else{
                        $sku .= '-' . $additionalOption['option_sku'];
                    }
                }
            }
        }

        return $sku;
    }

}