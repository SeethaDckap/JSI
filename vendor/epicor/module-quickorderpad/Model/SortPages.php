<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Model;

class SortPages
{
    public static function sortItemsIntoPages(array $inputArray, $pageData =[], $sort = true): array
    {
        $pageSize = $pageData['page_size'] ?? 5;
        $currentPage = 1;

        $pagedArray = [];
        if($sort){
            asort($inputArray);
        }

        $sortedValues = array_values($inputArray);
        $sortedKeys = array_keys($inputArray);
        $count = 0;
        $turnPage = $pageSize;
        foreach ($sortedValues as $key => $item) {
            if ($count >= $turnPage) {
                $turnPage = $pageSize + $turnPage;
                $currentPage++;
            }
            $pagedArray[$currentPage][$sortedKeys[$key]] = $item;
            $count++;
        }

        return $pagedArray;
    }
}