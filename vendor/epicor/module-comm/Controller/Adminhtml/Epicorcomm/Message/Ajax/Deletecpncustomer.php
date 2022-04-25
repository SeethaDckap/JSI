<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class Deletecpncustomer extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{


    public function execute()
    {

        $this->_redirect('adminhtml/customer_group/edit/', array(
            'id' => $this->getRequest()->getParam('customer', null)
            )
        );
    }

    }
