<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Framework\Data\Form\Element;

/**
 * Class Password
 * @package Epicor\Common\Plugin\Framework\Data\Form\Element
 */
class Password
{
    /**
     * Merging passwords data types
     * @param \Magento\Framework\Data\Form\Element\Password $subject
     * @param $result
     * @return array
     */
    public function afterGetHtmlAttributes(
        \Magento\Framework\Data\Form\Element\Password $subject,
        $result
    ) {
        $type = [
            'data-password-min-length',
            'data-password-min-character-sets'
        ];
        return array_merge($result, $type);
    }
}