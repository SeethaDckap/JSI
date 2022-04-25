<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Js;


/**
 * Js Translation block, used for getting the translations of javascript messages
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Translation extends \Magento\Framework\View\Element\Template
{

    protected $_translations = array();
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();

        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        } else {
            $this->setTemplate('Epicor_Comm::epicor_comm/js/translation.phtml');
        }
    }

    public function getTranslations()
    {
        return $this->_translations;
    }

    public function setTranslations($translations)
    {
        $this->_translations = $translations;
        return $this;
    }

    public function addTranslation($message, $translatedMessage = null)
    {
        if (!$translatedMessage) {
            $translatedMessage = __($message);
        }
        $this->_translations[$message] = $translatedMessage;
        return $this;
    }

}
