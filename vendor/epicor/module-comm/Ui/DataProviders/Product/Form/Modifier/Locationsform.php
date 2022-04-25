<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\DataProviders\Product\Form\Modifier;


use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form;
use Magento\Framework\View\LayoutFactory;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\UrlInterface;

/**
 * Class Productlocations
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Locationsform extends AbstractModifier
{
    const DATA_SCOPE       = '';

    /**
     * @var string
     */
    private static $previousGroup = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 18;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;
    
    /**
     * Locations constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
     
        $meta = array_replace_recursive(
            $meta,
            [
                'locations' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-section',
                                'label' => __('Locations'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' =>
                                    $this->getNextGroupSortOrder(
                                        $meta,
                                        self::$previousGroup,
                                        self::$sortOrder
                                    ),
                            ],
                        ],
                    ],
                    'children' => [
                        'productlocations_listing' => $this->getLocationsFieldset()
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Prepares locations fieldset
     * @return array
     */
    private function getLocationsFieldset()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Location'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            $this->layoutFactory->create()->createBlock(
                                'Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Locations\Form'
                            )->toHtml(),
                    ]
                ]
            ]
        ];
    }

}
