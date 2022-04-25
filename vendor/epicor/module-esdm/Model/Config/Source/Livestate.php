<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Model\Config\Source;

/**
 * ESDM live states config source
 * 
 * @category    Epicor
 * @package     Epicor_Esdm
 * @author      Epicor Web Sales Team
 */
class Livestate
{

	const TEST_MODE = 0;
	const LIVE_MODE = 1;
	const DEMO_MODE = 2;  	

    public function toOptionArray()
    {
        return array(
            array('value' => self::DEMO_MODE, 'label' => "Demo Mode"),
            array('value' => self::TEST_MODE, 'label' => "Test Mode"),
            array('value' => self::LIVE_MODE, 'label' => "LIVE MODE")
        );
    }

}
