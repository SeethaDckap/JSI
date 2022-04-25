<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class CartMergeOptions
{
    const MERGE = "merge";
    const PROMPT = "prompt";
    const CLEAR = "clear";

    /**
     * options for sales/sales/reorder/cart merge action
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::MERGE, 'label' => "Merge Cart"),
            array('value' => self::PROMPT, 'label' => "Prompt"),
            array('value' => self::CLEAR, 'label' => "Clear Cart"),
        );
    }

}
