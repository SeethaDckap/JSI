<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax;

class Deletecpnproduct extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Ajax
{

    public function execute()
    {
        $this->deleteCpn();
        $this->_redirect('adminhtml/catalog_product/edit/', array(
            'id' => $this->getRequest()->getParam('product', null)
            )
        );
    }

    }
