<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Filter;


/**
 * Model Class for List Filtering
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
abstract class AbstractModel extends \Magento\Framework\DataObject
{
    public function __construct(
        array $data = []
    ) {
        parent::__construct(
            $data
        );
    }


    abstract function filter($collection);
}
