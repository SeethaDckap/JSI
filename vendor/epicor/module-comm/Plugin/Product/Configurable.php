<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\Comm\Plugin\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Swatches\Model\SwatchAttributesProvider;

class Configurable
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $prodHelper;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface $_cacheState
     */
    protected $_cacheState;

    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributesProvider;

    /**
     * Configurable constructor.
     * @param \Epicor\Comm\Helper\Product $prodHelper
     * @param \Magento\Framework\App\Cache\StateInterface $_cacheState
     */
    public function __construct(
        \Epicor\Comm\Helper\Product $prodHelper,
        \Magento\Framework\App\Cache\StateInterface $_cacheState,
        SwatchAttributesProvider $swatchAttributesProvider = null
    ) {
        $this->prodHelper = $prodHelper;
        $this->_cacheState = $_cacheState;
        $this->swatchAttributesProvider = $swatchAttributesProvider
            ?: ObjectManager::getInstance()->get(SwatchAttributesProvider::class);
    }

    /**
     * @param \Magento\Swatches\Block\Product\Renderer\Configurable $subject
     * @param $result
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result
    ) {
        if ($this->isCartConfigureUpdate($subject->getRequest())) {
            return "Epicor_Comm::product/view/options/type/update-configurable.phtml";
        }
        $isFPC = $this->_cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
        $isLazyLoad = $this->prodHelper->isLazyLoad("view");
        if(($isLazyLoad || $isFPC) && !$this->isProductHasSwatchAttribute($subject->getProduct())) {
            return "Epicor_Comm::product/view/options/type/configurable.phtml";
        }
        return $result;
    }

    /**
     * @param $request
     * @return bool
     */
    public function isCartConfigureUpdate($request): bool
    {
        if ($request instanceof \Magento\Framework\App\Request\Http) {
            return $request->getModuleName() . '-' . $request->getActionName() === 'checkout-configure';
        }
    }

    public function isProductHasSwatchAttribute($product)
    {
        $swatchAttributes = $this->swatchAttributesProvider->provide($product);
        return count($swatchAttributes) > 0;
    }
}