<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Errors List
 *
 * @author Pradeep.Kumar
 */
class Errors extends \Magento\Framework\View\Element\Template
{

    

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,        
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        
        $this->_checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Retrieve error lists 
     *     
     * @return array
     */
    public function getErrorLists()
    {
        $errors = $this->_checkoutSession->getQopErrors();
        if(!$errors) 
            $errors=[];
         $this->_checkoutSession->setQopErrors(null);
        
        return $errors;
    }

}
