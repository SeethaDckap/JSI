<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


/**
 * Generic grid helper
 * 
 * used for processing rows displayed by the generic grid for the various message types
 */
class Genericgrid extends \Epicor\Common\Helper\Data
{

    /**
     * Compare function - integers
     * 
     * used by Linq to sort integers
     * @param integer $a
     * @param integer $b
     * @return integer
     */
    public static function intcmp($a, $b)
    {
        if (!is_numeric($a)) {
            // may be a number that contains text, so treat as text
            $res = strcmp($a, $b);
        } else {
            $res = ($a - $b);
        }
        return $res;
    }

    /**
     * Compare function - dates
     * 
     * used by Linq to sort dates
     * @param string $a
     * @param string $b
     * @return integer
     */
    public static function datecmp($a, $b)
    {
        return (strtotime($a) - strtotime($b));
    }

}
