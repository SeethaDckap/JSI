<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Config\Source;

/**
 * Category options provider.
 */
class DeploymentOptions implements \Magento\Framework\Option\ArrayInterface
{


    /**
     * Gets option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'test',
                'label' => __('Test'),
            ],
            [
                'value' => 'production',
                'label' => __('Production'),
            ],
        ];

    }//end toOptionArray()


}//end class
