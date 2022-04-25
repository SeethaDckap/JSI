<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class Products extends \Epicor\Lists\Controller\Contract
{

    public function __construct(

    ) {
    }
    /**
     * Products initial grid tab load
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();
        $this->loadLayout();
        $this->getLayout()->getBlock('contract_products')
            ->setSelected($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    }
