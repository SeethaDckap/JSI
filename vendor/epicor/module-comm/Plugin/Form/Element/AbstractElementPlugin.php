<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Plugin
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class AbstractElementPlugin
 * @package Epicor\Comm\Plugin\Form\Element
 */
class AbstractElementPlugin
{
    /**
     * Added accept attribute for Html
     *
     * @param AbstractElement $subject
     * @param $result
     * @return mixed
     */
   public function afterGetHtmlAttributes(AbstractElement $subject, $result)
   {
       array_push($result, 'accept');
       return $result;
   }
}
