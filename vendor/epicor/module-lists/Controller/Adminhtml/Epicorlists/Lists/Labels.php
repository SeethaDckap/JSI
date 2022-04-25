<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Labels extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(

    ) {
    }
    /**
     * Labels tab loader
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();
        $this->loadLayout();
        $this->renderLayout();
    }

    }
