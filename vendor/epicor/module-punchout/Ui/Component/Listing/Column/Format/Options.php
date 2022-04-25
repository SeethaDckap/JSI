<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Ui\Component\Listing\Column\Format;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    const TYPE_CXML = 'CXML';


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_CXML,
                'label' => __('CXML'),
            ],
        ];

    }//end toOptionArray()


}//end class

