<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin;

use Magento\Framework\App\ProductMetadataInterface;

class AggregatedFileCollectorPlugin
{
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {

        $this->productMetadata = $productMetadata;
    }

    public function afterCollectFiles($subject, $result)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.1') >= 0) {
            return $this->removeCommonCustomerFormXmlFile($result);
        }

        return $result;
    }

    private function removeCommonCustomerFormXmlFile($result)
    {
        $pattern = '/[cC]ommon\/view\/adminhtml\/ui_component\/customer_form.xml/';
        foreach($result as $key => $fileData){
            if(preg_match($pattern, $key)){
                unset($result[$key]);
            }
        }

        return $result;
    }
}