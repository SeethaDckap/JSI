<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Block\Adminhtml\Product\Helper\Form\Gallery;

/**
 * Description of Content
 *
 * @author 
 */
class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content {
    
  public function setTemplate($template)
    {
        $this->_template =  'Epicor_Comm::catalog/product/helper/gallery.phtml';
        return $this;
    }
 
}
