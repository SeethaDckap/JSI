<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Helper;


class GridMapping extends \Epicor\Comm\Helper\Messaging
{
    protected $customfieldsFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Supplierconnect\Model\ResourceModel\Customfields\CollectionFactory $customfieldsFactory
    )
    {
        $this->customfieldsFactory = $customfieldsFactory;
        parent::__construct($context);
    }

    public function getMappingValues($messageId,$messageSection) {
        $collection = $this->customfieldsFactory->create();
        $collection->addFieldToFilter('message_section', array('eq' => $messageSection));
        $collection->addFieldToFilter('message', array('eq' => $messageId))->load();
        $optionValues = array();
        if (count($collection->getItems()) > 0) {
            foreach ($collection->getItems() as $requestItems) {
                $trimFields = preg_replace('/\s+/', '', $requestItems->getCustomFields());
                $q = implode('>', array_map('ucfirst', explode('>', $trimFields)));
                $goodVals = str_replace('>', '', $q);
                $decamelize = $this->decamelize($goodVals);
                $optionValues[$requestItems->getCustomFields()] = $requestItems->getCustomFields();
            }
        }
        return $optionValues;
    }

    public function getInformationMappingValues($messageId,$messageSection) {
        $collection = $this->customfieldsFactory->create();
        $collection->addFieldToFilter('message_section', array('eq' => $messageSection));
        $collection->addFieldToFilter('message', array('eq' => $messageId))->load();
        $optionValues = array();
        if (count($collection->getItems()) > 0) {
            foreach ($collection->getItems() as $requestItems) {
                $trimFields = preg_replace('/\s+/', '', $requestItems->getCustomFields());
                $q = implode('>', array_map('ucfirst', explode('>', $trimFields)));
                $goodVals = str_replace('>', '', $q);
                $decamelize = $this->decamelize($goodVals);
                $optionValues[$requestItems->getCustomFields()] = $requestItems->getCustomFields();
            }
        }
        return $optionValues;
    }

    public function decamelize($string) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

}