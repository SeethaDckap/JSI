<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model\Config;


/**
 * F.A.Q. front end presentation options,  used for dropdown display in general config
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method   array(string)  toOptionArray()
 */
class Presentation
{

    public function toOptionArray()
    {
        return array(
            'accordion' => 'Accordion',
            'paragraphs' => 'Indexed paragraphs'
        );
    }

}
