<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\Config\Source;


/**
 * Elements live states config source
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Livestate
{

    public function toOptionArray()
    {
        return array(
            array('value' => \Epicor\Elements\Model\Api::DEMO_MODE, 'label' => "Demo Mode"),
            array('value' => \Epicor\Elements\Model\Api::TEST_MODE, 'label' => "Test Mode"),
            array('value' => \Epicor\Elements\Model\Api::LIVE_MODE, 'label' => "LIVE MODE")
        );
    }

}
