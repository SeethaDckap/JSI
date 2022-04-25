<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Acl\AclResource\Config;

/**
 * ACL resources configuration schema locator
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:module:Epicor_AccessRight:etc/access_right_merged.xsd');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:module:Epicor_AccessRight:etc/access_right.xsd');
    }
}
