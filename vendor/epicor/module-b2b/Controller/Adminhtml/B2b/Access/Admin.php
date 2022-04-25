<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Adminhtml\B2b\Access;


/**
 * 
 * Access rights controller - B2b
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Admin extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_aclId = 'customer/access/admin';

    protected function _initPage()
    {
        $this->loadLayout()
            ->_setActiveMenu('epicor_common/access/admin')
            ->_addBreadcrumb(__('Access Management'), __('Administration'));
        return $this;
    }
}
