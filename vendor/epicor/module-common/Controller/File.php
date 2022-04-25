<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller;

/**
 * File request controller
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class File extends \Magento\Framework\App\Action\Action
{

    function __construct(\Magento\Framework\App\Action\Context $context)
    {
        parent::__construct($context);
    }

}
