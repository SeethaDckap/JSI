<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 46)
namespace Epicor\Lists\Model;


class ListFilterReader
{
    protected $filters;

    public function __construct(
        $filters
    )
    {
        $this->filters = $filters;
    }

    /**
     * @param $filter
     * @return \Epicor\Lists\Model\ListModel\Filter\AbstractModel
     */
    public function getFilter($filter)
    {
        return $this->filters[$filter];
    }
}

//M1 > M2 Translation End