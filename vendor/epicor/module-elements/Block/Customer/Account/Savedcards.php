<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */

 
namespace Epicor\Elements\Block\Customer\Account;


/**
 * My Account > Saved Elements cards block
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Savedcards extends \Magento\Framework\View\Element\Template
{

    private $_savedcards;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    public function getCustomerCards()
    {
        if (!$this->_savedcards) {
            $this->_savedcards = $this->helper('elements')->getCustomerSavedCards();
        }
        return $this->_savedcards;
    }

    public function getDeleteUrl($card_id)
    {
        return $this->getUrl('elements/savedcards/delete/', array('card_id' => $card_id));
    }

}
