<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Silk\SyncDecoder\Api\Data;

/**
 * Interface AdditionalOptionInterface
 * @api
 * @since 100.0.2
 */
interface AdditionalOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get option title
     *
     * @return string|null
     */
    public function getOptionTitle();

    /**
     * Get option label
     *
     * @return string|null
     */
    public function getOptionLabel();

    /**
     * Get option SKU
     *
     * @return string|null
     */
    public function getOptionSku();

    /**
     * Get Base SKU
     *
     * @return string|null
     */
    public function getBaseSku();

    /**
     * Set option title
     *
     * @param string $optionTitle
     * @return $this
     */
    public function setOptionTitle($optionTitle);

    /**
     * Set option label
     *
     * @param string $optionLabel
     * @return $this
     */
    public function setOptionLabel($optionLabel);

    /**
     * Set option SKU
     *
     * @param string $optionSku
     * @return $this
     */
    public function setOptionSku($optionSku);

    /**
     * Set Base SKU
     *
     * @param string $baseSku
     * @return $this
     */
    public function setBaseSku($baseSku);
}
