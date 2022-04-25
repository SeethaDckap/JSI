<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ViewModel\Form;

use Epicor\Comm\Api\ViewModel\Form\FormKeyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\FormKey as FormKeyBlock;

/**
 * Class FormKey
 * @package Epicor\Comm
 */
class FormKey implements FormKeyInterface, ArgumentInterface
{
    /**
     * @var FormKeyBlock
     */
    private $formKeyBlock;

    /**
     * FormKey constructor.
     *
     * @param FormKeyBlock $formKeyBlock
     */
    public function __construct(FormKeyBlock $formKeyBlock)
    {
        $this->formKeyBlock = $formKeyBlock;
    }

    /**
     * @inheritDoc
     */
    public function getFormKey()
    {
        return $this->formKeyBlock->getFormKey();
    }
}