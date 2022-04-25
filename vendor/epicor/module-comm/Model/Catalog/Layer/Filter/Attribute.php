<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalog\Layer\Filter;


/**
 * Layer attribute filter
 *
 * Overidden to allow text attributes to work with layered navigation
 * 
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Catalog\Model\Layer\Filter\Attribute
{

    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $frameworkNameBuilderHelper;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $filterAttributeFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Framework\Code\NameBuilder $frameworkNameBuilderHelper,
        array $data = []
    ) {
        $this->frameworkNameBuilderHelper = $frameworkNameBuilderHelper;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterAttributeFactory,
            $string,
            $tagFilter,
            $data
        );
    }


    /**
     * Apply attribute option filter to product collection
     *
     * @param   \Zend_Controller_Request_Abstract $request
     * @param   \Magento\Framework\DataObject $filterBlock
     * @return  \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        $isBooleanFalse = false;
        if ($attribute->getFrontend()->getInputType() == 'text') {
            $text = $filter;
        } else if ($attribute->getFrontend()->getInputType() == 'boolean') {
            $text = ($filter == 1) ? 'Yes' : 'No';
            if ($filter === '0') {
                $isBooleanFalse = true;
            }
        } else {
            $text = $this->getOptionText($filter);
        }
        if (($filter && strlen($text)) || $isBooleanFalse) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }
        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey() . '_' . $this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {

            $optionsCount = $this->_getResource()->getCount($this);
            $inputType = $attribute->getFrontend()->getInputType();

            if ($inputType == 'text' || $inputType == 'boolean') {
                $options = array();
                foreach ($optionsCount as $optionVal => $count) {

                    if ($inputType == 'boolean') {
                        $optionLabel = ($optionVal == 1) ? 'Yes' : 'No';
                    } else {
                        $optionLabel = $optionVal;
                    }

                    $options[] = array(
                        'label' => $optionLabel,
                        'value' => $optionVal,
                    );
                }
            } else {
                $options = $attribute->getFrontend()->getSelectOptions();
            }

            $data = array();
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if ($this->frameworkNameBuilderHelper->strlen($option['value'])) {
                    // Check filter type
                    if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                        if (!empty($optionsCount[$option['value']])) {
                            $data[] = array(
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'count' => $optionsCount[$option['value']],
                            );
                        }
                    } else {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        );
                    }
                }
            }

            $tags = array(
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG . ':' . $attribute->getId()
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

}
