<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Index;

class Index extends \Epicor\Faqs\Controller\Index
{





    /**
     * Index action
     * 
     * Sets up the necessary blocks, stylesheets and javascripts depending on
     * wether the accordion or paragraphs view was configured in the admin
     * config F.A.Q. panel.
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
