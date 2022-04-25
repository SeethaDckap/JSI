<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model;


class ModelReader
{

    protected $reader;

    public function __construct(
        $readers
    )
    {
        $this->reader = $readers;
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model)
    {
        return $this->reader[$model];
    }
}
