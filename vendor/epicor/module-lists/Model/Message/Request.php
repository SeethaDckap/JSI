<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message;


/**
 * Base class for zall request messages.
 * @author David. wylie
 * 
 * @method setDirection($direction)
 * @method setErp($erp)
 * @method setLegacyHeader($legacyHeader)
 * @method getCustomerGroupId()
 * @method setIsDeamon(bool $isDeamon)
 * @method bool getIsDeamon()
 * @method setConnectionSuccessful(bool $success)
 * @method bool getConnectionSuccessful()
 * 
 * @method int getStoreId()
 */
abstract class Request extends \Epicor\Comm\Model\Message\Request
{
    
}
