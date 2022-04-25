<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Block\Generic\Listing;


class ColumnRendererReader
{
    /**
     * @param $renderer
     * @return string
     */
    public function getRenderer($renderer)
    {
        return $this->toNamespace($renderer);
    }

    protected function toNamespace($alias, $type = 'model')
    {
        $strs = [];
        $string = explode('/', $alias);
        if (isset($string[0])) {
            foreach (explode('_', $string[0]) as $item) {
                $strs[] = ucfirst($item);
            }
            if ($strs[0] != 'Epicor') {
                array_unshift($strs, 'Epicor');
            }
        }

        if (isset($string[1])) {
            $strs[] = ucfirst($type);
            foreach (explode('_', $string[1]) as $item) {
                $strs[] = ucfirst($item);
            }
        }

        $strs = implode("\\", $strs);

        $strs = str_replace('List', 'Listing', $strs);

        return $strs;
    }
}