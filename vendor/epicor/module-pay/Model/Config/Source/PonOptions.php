<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
declare(strict_types = 1);

namespace Epicor\Pay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PonOptions
 */
class PonOptions implements OptionSourceInterface
{
    const PON_VISIBLE = 'visible';
    const PON_MANDATORY = 'mandatory';
    const PON_NOT_VISIBLE = 'not-visible';

    /**
     * @return array
     */
    public function toOptionArray() :array
    {
        return [
            ['value' => self::PON_NOT_VISIBLE, 'label' => __('Not visible')],
            ['value' => self::PON_VISIBLE, 'label' => __('Visible')],
            ['value' => self::PON_MANDATORY, 'label' => __('Visible and mandatory')]
        ];
    }
}
