<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Massaction\Item\Additional;


class DefaultBlock extends \Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\DefaultAdditional
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }
    public function createFromConfiguration(array $configuration)
    {

        $form = $this->formFactory->create();

        foreach ($configuration as $itemId => $item) {

            if (isset($item['renderer'])) {
                $form->addType($item['renderer']['type'], $item['renderer']['class']);
            }

            $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
            $form->addField($itemId, $item['type'], $item);
        }
        $this->setForm($form);
        return $this;
    }

}
