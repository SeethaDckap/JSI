<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\DataProviders\Product\Form\Modifier;


use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;

/**
 * Class Productlocations
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Productlocations extends AbstractModifier
{
  
    const GROUP_PRODUCTLOCATIONS = 'productlocations';
    const GROUP_CONTENT = 'content';
    const DATA_SCOPE_PRODUCTLOCATIONS = 'grouped';
    const SORT_ORDER = 20;
    const LINK_TYPE = 'associated';
    
    const ASSOCIATED_PRODUCT_MODAL = 'configurable_associated_product_modal';
    const ASSOCIATED_PRODUCT_LISTING = 'configurable_associated_product_listing';
    const CONFIGURABLE_MATRIX = 'configurable-matrix';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $dataScopeName;

    /**
     * @var string
     */
    private $dataSourceName;

    /**
     * @var string
     */
    private $associatedListingPrefix;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->locator->getProduct()->getId()) {
            return $meta;
        }

        $meta[static::GROUP_PRODUCTLOCATIONS] = [
            'children' => [                
            //    'productlocations_edit' => $this->getButtonSet(),
                'productlocations_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'productlocations_listing',
                                'externalProvider' => 'productlocations_listing.productlocations_listing_data_source',
                                'selectionsProvider' => 'productlocations_listing.productlocations_listing.product_columns.ids',
                                'ns' => 'productlocations_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id'
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id'
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Locations'),
                        'collapsible' => true,
                        'opened' => false,
                        'componentType' => Form\Fieldset::NAME,
                        'sortOrder' =>
                            $this->getNextGroupSortOrder(
                                $meta,
                                static::GROUP_CONTENT,
                                static::SORT_ORDER
                            ),
                    ],
                ],
            ],
        ];
    //    $meta['edit_location_modal'] = $this->getEditForm();
        return $meta;
    }
    protected function getEditForm()
    {
        return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('Edit Location'),
                            ],
                            'imports' => [
                                'state' => '!index=edit_location:responseStatus'
                            ],
                        ],
                    ],
                ],
                'children' => [
                    'edit_location' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/insert-form',
                                    'dataScope' => '',
                                    'update_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                    'render_url' => $this->urlBuilder->getUrl(
                                        'mui/index/render_handle',
                                        [
                                            'handle' => 'catalog_category_create',
                                            'store' => $this->locator->getStore()->getId(),
                                            'buttons' => 1
                                        ]
                                    ),
                                    'autoRender' => false,
                                    'ns' => 'new_category_form',
                                    'externalProvider' => 'edit_location_form.edit_location_form_data_source',
                                    'toolbarContainer' => '${ $.parentName }',
                                    'formSubmitType' => 'ajax',
                                ],
                            ],
                        ]
                    ]
                ]
            ];
    }
    
    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'title' => __('Edit Location'),
                        'formElement' => 'container',
                        'additionalClasses' => 'admin__field-small',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/button',
                        'template' => 'ui/form/components/button/container',
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.edit_location_modal',
                                'actionName' => 'toggleModal',
                            ],
                            [
                                'targetName' =>
                                    'product_form.product_form.edit_location_modal.edit_location',
                                'actionName' => 'render'
                            ],
                            [
                                'targetName' =>
                                    'product_form.product_form.edit_location_modal.edit_location',
                                'actionName' => 'resetForm'
                            ]
                        ],
                        'additionalForGroup' => true,
                        'provider' => false,
                        'source' => 'product_details',
                        'displayArea' => 'insideGroup',
                        'sortOrder' => 20,
                    ],
                ],
            ]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();

        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_product_id'] = $productId;

        return $data;
    }
}