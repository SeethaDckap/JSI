<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\EccHidePrices;

use Epicor\Comm\Helper\Data as CommData;
use Magento\Framework\DataObject as DataObject;

class HidePrice
{
    private $commHelper;

    public function __construct(CommData $commHelper)
    {
        $this->commHelper = $commHelper;
    }

    public function isHidePricesActive()
    {
        return (bool)$this->getHidePrices() && in_array($this->commHelper->getEccHidePrice(), [1, 3]);
    }

    public function isHidePricesCheckoutYesActive(): bool
    {
        return $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [3]);
    }

    public function getHidePrices(){
        return $this->commHelper->getEccHidePrice();
    }

    public function hidePriceColumns($columnObject, $columnsToHide)
    {
        if($columnObject instanceof DataObject && is_array($columnsToHide)){
            $this->hidePriceRelatedGridColumns($columnObject, $columnsToHide);
        }
    }

    /**
     * @param $columnObject DataObject
     * @param array $columnsToHide
     */
    private function hidePriceRelatedGridColumns(DataObject $columnObject, array $columnsToHide)
    {
        foreach ($columnsToHide as $indexCol) {
            $columnObject->unsetData($indexCol);
        }
    }
}