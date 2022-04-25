<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request\Validators;

use Epicor\Punchout\Model\ValidatorInterface;
use Epicor\Punchout\Model\Request\Validator;

/**
 * Class for header validation
 */
class EmptyCartValidator extends Validator implements ValidatorInterface
{


    /**
     * Validate data
     *
     * @param \SimpleXMLElement $request Request data object.
     *
     * @return array
     */
    public function validate(\SimpleXMLElement $request)
    {
        $error     = 0;
        $errorCode = '200';
        $itemArray = $request->Request->OrderRequest->ItemOut;

        if (empty((array) $itemArray)) {
            $error     = 1;
            $errorCode = '400';
        }

        return [
            'error'       => $error,
            'code'        => $errorCode,
        ];

    }//end validate()


}//end class
