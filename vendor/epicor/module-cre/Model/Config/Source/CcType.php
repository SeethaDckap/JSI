<?php
/**
 * Copyright © 2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Model\Config\Source;

/**
 * Class CcType
 */
class CcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * List of specific credit card types
     * @var array
     */
    private $specificCardTypesList = [
        'MC' => 'Master Card & Maestro',
        'UP' => 'Union Pay'
    ];

    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        // all card types: ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI','SM','DN','CUP', 'JCB','SO','OT'];
        return array_values(\Epicor\Cre\Helper\CreData::ECC_CRE_CARD_TYPE_MAP);
    }

    /**
     * Returns credit cards types
     *
     * @return array
     */
    public function getCcTypeLabelMap()
    {
        return array_merge($this->_paymentConfig->getCcTypes(), $this->specificCardTypesList);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->getCcTypeLabelMap() as $code => $name) {
            if (in_array($code, $allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
