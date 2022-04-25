<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Eav\Attribute\Data;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Erpaccount
 *
 * @author Paul.Ketelle
 */
class Erpaccount extends \Magento\Eav\Model\Attribute\Data\Text
{

    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\Stdlib\StringUtils $stringHelper)
    {
        parent::__construct(
            $localeDate, $logger, $localeResolver, $stringHelper
        );
    }

//put your code here
}
