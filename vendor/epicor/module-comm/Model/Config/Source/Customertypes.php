<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Customertypes
{

    public function toOptionArray($includeExtraOptions = true)
    {
        $types = explode(',', \Epicor\Comm\Model\Customer\Erpaccount::ACCOUNT_TYPES);

        $options = array();

        if ($includeExtraOptions) {
            $options[] = array(
                'value' => 'all',
                'label' => 'All'
            );

            $options[] = array(
                'value' => 'nobody',
                'label' => 'Nobody'
            );
        }

        foreach ($types as $type) {
            $options[] = array(
                'value' => $type,
                'label' => $type
            );
        }

        return $options;
    }

}
