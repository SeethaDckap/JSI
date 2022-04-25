<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Ui\DataProviders\Product\Form\Modifier;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class Related override for substitute
 */
class Related extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related
{
    const DATA_SCOPE_SUBSTITUTE = 'substitute';

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopePrefix;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param Status $status
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param string $scopeName
     * @param string $scopePrefix
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        $scopeName = '',
        $scopePrefix = ''
    ) {
        $this->scopePrefix = $scopePrefix;
        parent::__construct(
            $locator,
            $urlBuilder,
            $productLinkRepository,
            $productRepository,
            $imageHelper,
            $status,
            $attributeSetRepository,
            $scopeName,
            $scopePrefix);
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        //$meta = parent::modifyMeta($meta);
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_RELATED => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_SUBSTITUTE => $this->getSubstituteFieldset(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Related Products, Up-Sells, Cross-Sells and Substitute Items')
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Retrieve all data scopes
     *
     * @return array
     * @since 101.0.0
     */
    protected function getDataScopes()
    {
//        $dataScopes = parent::getDataScopes();
//        $dataScopes[] = static::DATA_SCOPE_SUBSTITUTE;
//        return $dataScopes;
        return [
            static::DATA_SCOPE_SUBSTITUTE
        ];
    }


    /**
     * Prepares config for the Substitute products fieldset
     *
     * @return array
     * @since 101.0.0
     */
    protected function getSubstituteFieldset()
    {
        $content = __('These are the items that are the alternatives available for a given product.');
        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Add Substitute Items'),
                    $this->scopePrefix . static::DATA_SCOPE_SUBSTITUTE
                ),
                'modal' => $this->getGenericModal(
                    __('Add Substitute Items'),
                    $this->scopePrefix . static::DATA_SCOPE_SUBSTITUTE
                ),
                static::DATA_SCOPE_SUBSTITUTE => $this->getGrid($this->scopePrefix . static::DATA_SCOPE_SUBSTITUTE),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Substitute Items'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 40,
                    ],
                ],
            ]
        ];
    }
}
