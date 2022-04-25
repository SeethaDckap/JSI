<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Acl\AclResource;

/**
 * Acl resources provider interface
 *
 * @api
 * @since 100.0.2
 */
interface ProviderInterface
{
    /**
     * Retrieve ACL resources
     *
     * @return array
     */
    public function getAclResources();
}
