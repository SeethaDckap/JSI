<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\OrderApproval\Block\Group\Rules\Renderer;;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Renderer for Editable sales rules
 *
 * @api
 * @since 100.0.2
 */
class Editable extends AbstractBlock implements RendererInterface
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    private $inlineTranslate;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Translate\InlineInterface $inlineTranslate
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Translate\InlineInterface $inlineTranslate,
        array $data = []
    ) {
        $this->inlineTranslate = $inlineTranslate;
        parent::__construct($context, $data);
    }

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @see RendererInterface::render()
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->addClass('element-value-changer');
        $element->addClass('validate-digits');
        $valueName = $element->getValueName();

        if ($valueName === '') {
            $valueName = '...';
        }

        if ($element->getShowAsText()) {
            $html = ' <input type="hidden" class="hidden" id="' .
                $this->escapeHtmlAttr($element->getHtmlId()) .
                '" name="' .
                $this->escapeHtmlAttr($element->getName()) .
                '" value="' .
                $this->escapeHtmlAttr($element->getValue()) .
                '" data-form-part="' .
                $this->escapeHtmlAttr($element->getData('data-form-part')) .
                '"/> ' .
                $this->escapeHtml(
                    $valueName
                ) . '&nbsp;';
        } else {
            $html = ' <span class="rule-param"' .
                ($element->getParamId() ? ' id="' .
                $element->getParamId() .
                '"' : '') .
                '>' ;

            $element->addClass('approval-limit');
            $html .= '<span class="element approval-limit-span"><div class="approval-limit-container">'
                . $element->getElementHtml() . '<div><b>Including shipping and tax</b></div></div>';

            if ($element->getExplicitApply()) {
                $html .= ' <a href="javascript:void(0)" class="rule-param-apply"><img src="' . $this->getViewFileUrl(
                    'images/rule_component_apply.gif'
                ) . '" class="v-middle" alt="' . __(
                    'Apply'
                ) . '" title="' . __(
                    'Apply'
                ) . '" /></a> ';
            }

            $html .= '</span></span>&nbsp;';
        }

        return $html;
    }
}
