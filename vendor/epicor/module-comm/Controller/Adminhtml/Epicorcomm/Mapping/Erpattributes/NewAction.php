<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class NewAction extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/new 
     * 
     */

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->_forward('edit');
    }

    }
