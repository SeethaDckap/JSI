<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Block\Adminhtml\Elements\Edit;


/**
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Elements::elements/transaction/form.phtml');
    }

    public function getTransaction()
    {
        return $this->registry->registry('current_transaction');
    }

}
