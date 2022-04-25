<?php
namespace Silk\SyncDecoder\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Silk\SyncDecoder\Api\Data\AdditionalOptionInterface;

class AdditionalOption extends AbstractExtensibleModel implements AdditionalOptionInterface
{
    const OPTION_TITLE = 'option_title';
    const OPTION_LABEL = 'option_label';
    const OPTION_SKU = 'option_sku';
    const BASE_SKU = 'base_sku';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionTitle()
    {
        return $this->getData(self::OPTION_TITLE);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionLabel()
    {
        return $this->getData(self::OPTION_LABEL);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionSku()
    {
        return $this->getData(self::OPTION_SKU);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getBaseSku()
    {
        return $this->getData(self::BASE_SKU);
    }

    /**
     * Set option title
     *
     * @return $this
     */
    public function setOptionTitle($optionTitle)
    {
        return $this->setData(self::OPTION_TITLE, $optionTitle);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setOptionLabel($optionLabel)
    {
        return $this->setData(self::OPTION_LABEL, $optionLabel);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setOptionSku($optionSku)
    {
        return $this->setData(self::OPTION_SKU, $optionSku);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setBaseSku($baseSku)
    {
        return $this->setData(self::BASE_SKU, $baseSku);
    }
}
