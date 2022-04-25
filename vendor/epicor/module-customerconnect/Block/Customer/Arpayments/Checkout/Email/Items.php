<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales Order Email order items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Checkout\Email;

class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Retrieve item renderer block
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getItemRenderer($type)
    {
        /** @var \Magento\Framework\View\Element\RendererList $rendererList */
        $rendererList = $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        $renderer = $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
        $renderer->setRenderedBlock($this);
        return $renderer;
    }
}
