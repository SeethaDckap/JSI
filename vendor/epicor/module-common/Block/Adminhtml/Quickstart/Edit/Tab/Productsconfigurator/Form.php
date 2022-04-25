<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab\Productsconfigurator;


class Form extends \Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab\AbstractBlock
{

    protected function getKeysToRender()
    {
        return array('products', 'Configurator', 'ewc');
    }

}
