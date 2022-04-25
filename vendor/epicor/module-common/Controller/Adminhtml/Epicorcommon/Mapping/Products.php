<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

use Magento\Backend\App\Action;

abstract class Products extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping
{

    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_Common::mapping_products';

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession) {
        parent::__construct($context, $backendAuthSession);
    }
}
