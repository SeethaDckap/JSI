<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Ui\Component\Listing\Column;

class Url extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $url = $item['url'];
                $item[$this->getData('name')] =  $url . <<<EOD
<br />                
<a href="$url">Go to Url</a>
EOD;

            }
        }

        return $dataSource;
    }
}