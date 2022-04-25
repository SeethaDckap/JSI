<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Themes
 * @subpackage Setup
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Model\Request;

interface JwtPayloadValidatorInterface
{


    /**
     * Validates token payload.
     *
     * @param array $jwtPayload Payload Array
     *
     * @return bool
     */
    public function validate(array $jwtPayload);


}//end interface
