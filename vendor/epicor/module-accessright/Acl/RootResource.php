<?php
/**
 * Root ACL Resource
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Acl;

/**
 * @api
 * @since 100.0.2
 */
class RootResource
{
    /**
     * Root resource id
     *
     * @var string
     */
    protected $_identifier;

    /**
     * Authorization level of a basic admin session
     */
    const FRONTEND_RESOURCE = 'Magento_Frontend::all';

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->_identifier = $identifier;
    }

    /**
     * Retrieve root resource id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_identifier;
    }
}
