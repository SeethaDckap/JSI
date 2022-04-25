<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping;

use Magento\Backend\App\Action;

abstract class Claimstatus extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\AbstractMapping {

    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_Common::mapping_claim_status';


    public function __construct(
            \Epicor\Comm\Controller\Adminhtml\Context $context,
            \Magento\Backend\Model\Auth\Session $backendAuthSession
            ) 
    {
        parent::__construct($context, $backendAuthSession);
    }


}
