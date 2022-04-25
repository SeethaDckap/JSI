<?php
/**
 * High-level interface for catalog attributes data that hides format from the client code
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;

class EccMappingConfig
{
    /**
     * @var \Epicor\Common\Model\EccMappingConfig\Data
     */
    protected $_dataStorage;

    /**
     * @param \Epicor\Common\Model\EccMappingConfig\Data $dataStorage
     */
    public function __construct(\Epicor\Common\Model\EccMappingConfig\Data $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Retrieve names of attributes belonging to specified group
     *
     * @param string $groupName Name of an attribute group
     * @return array
     */
    public function getAttributeNames($groupName=[])
    {
        return $this->_dataStorage->get($groupName, []);
    }
}
