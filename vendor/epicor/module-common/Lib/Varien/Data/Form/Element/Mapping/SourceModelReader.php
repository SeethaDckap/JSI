<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Lib\Varien\Data\Form\Element\Mapping;


class SourceModelReader
{
    protected $readers;

    public function __construct(
        $readers
    )
    {
        $this->readers = $readers;
    }

    /**
     * @param $sourceModel
     * @return mixed
     */
    public function getModel($sourceModel)
    {
        if (isset($this->readers[$sourceModel])) {
            return $this->readers[$sourceModel];
        } else {
            throw new \InvalidArgumentException('Unknow source model "'.$sourceModel.'"');
        }
    }
}