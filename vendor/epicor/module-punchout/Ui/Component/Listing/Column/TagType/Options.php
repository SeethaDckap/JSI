<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Ui\Component\Listing\Column\TagType;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    const TYPE_REGULAR        = 'regular';
    const TYPE_CLASSIFICATION = 'classification';
    const TYPE_EXTRINSIC      = 'extrinsic';


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_REGULAR,
                'label' => __('Regular'),
            ],
            [
                'value' => self::TYPE_CLASSIFICATION,
                'label' => __('Classification'),
            ],
            [
                'value' => self::TYPE_EXTRINSIC,
                'label' => __('Extrinsic'),
            ],
        ];

    }//end toOptionArray()


}//end class

