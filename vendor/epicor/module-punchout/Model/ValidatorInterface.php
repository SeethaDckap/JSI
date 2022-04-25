<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Magento\Framework\DataObject;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{


    /**
     * Validate header credentials
     *
     * @param \SimpleXMLElement $request Request.
     *
     * @return array
     */
    public function validate(\SimpleXMLElement $request);


}//end interface
