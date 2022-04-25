<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Helper
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;


/**
 * TransferCart Class.
 */
abstract class AbstractPunchout
{

    /**
     * Get Payload ID.
     *
     * @return mixed
     */
    public function getPayloadID()
    {
        $uuid1 = uniqid('', true);
        $uuid2 = uniqid('', true);
        $uuid3 = uniqid('', true);

        return str_replace(
            '.',
            '',
            sprintf(
                '%s-%s-%s-%s-%s-%s',
                substr($uuid1, -11, 5),
                substr($uuid1, -5, 6),
                substr($uuid2, -12, 5),
                substr($uuid2, -7, 6),
                substr($uuid3, -13, 5),
                substr($uuid3, -6, 4)
            )
        );

    }


}//end class