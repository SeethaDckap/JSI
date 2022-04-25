<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Ui\DataProviders\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Quantity
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Quantity extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }
    
    /**
     * {@inheritdoc}
     */    
    public function modifyMeta(array $meta)
    {
        if ($path = $this->arrayManager->findPath('quantity_and_stock_status_qty', $meta, null, 'children')) {
            $this->arrayManager->remove(
                $path . '/children/qty/arguments/data/config/validation/validate-digits',
                $meta
            );
            $this->arrayManager->merge($path . '/children/qty/arguments/data/config/imports',$meta,
                                       array('handleChanges'=>"1"));
        }

        if ($path = $this->arrayManager->findPath('advanced_inventory_modal', $meta)) {
            $meta = $this->arrayManager->merge(
                $path . '/children/stock_data/children/qty/arguments/data/config',
                $meta,
                ['validation' => ['validate-digits' => false],'imports'=>['handleChanges'=>'1']]
            );
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}