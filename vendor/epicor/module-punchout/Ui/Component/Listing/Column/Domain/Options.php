<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Ui\Component\Listing\Column\Domain;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    const TYPE_NETWORKID = 'NetworkId';
    const TYPE_DUNS      = 'DUNS';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_NETWORKID,
                'label' => __('NetworkId'),
            ],
            [
                'value' => self::TYPE_DUNS,
                'label' => __('DUNS'),
            ],
        ];

    }//end toOptionArray()


}//end class

