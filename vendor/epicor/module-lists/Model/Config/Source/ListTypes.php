<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;

use Epicor\Lists\Model\ListModel\Type;

/**
 * List Types
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class ListTypes
{
    /**
     * @var Type
     */
    private $type;

    /**
     * ListTypes constructor.
     * @param Type $type
     */
    public function __construct(
        Type $type
    )
    {
        $this->type = $type;
    }

    /**
     * Gets the type of list that can be created at frontend
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $listTypes = $this->type->toListFilterArray();
        foreach ($listTypes as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        return $options;
    }

}
