<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block;


/**
 * Protected block, used for displaying blocks that need to be protected by access rights
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class ProtectedBlock extends \Magento\Framework\View\Element\Template
{

    private $_protection = array();

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function addProtection($name, $rights)
    {
        $this->_protection[$name] = $rights;
    }

    protected function _toHtml()
    {
        $helper = $this->commonAccessHelper;

        $html = '';
        foreach ($this->getChildNames() as $name) {
            $block = $this->getLayout()->getBlock($name);

            if (!$block) {
                //M1 > M2 Translation Begin (Rule 55)
                //throw new \Magento\Framework\Exception\LocalizedException($this->__('Invalid block: %s', $name));
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid block: %1', $name));
                //M1 > M2 Translation End
            }

            $blockAllowed = true;

            if (isset($this->_protection[$name])) {
                $blockAllowed = $helper->customerHasAccess($this->_protection[$name]['module'], $this->_protection[$name]['controller'], $this->_protection[$name]['action'], $this->_protection[$name]['block'], $this->_protection[$name]['action_type']);
            }
            if ($blockAllowed) {
                $html .= $block->toHtml();
            }
        }

        return $html;
    }

}
