<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Serialize\Serializer;

use Epicor\Comm\Model\Serialize\SerializerInterface;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 *
 * @api
 * @since 100.2.0
 */
class Json implements SerializerInterface
{

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;
    

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }
    
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function serialize($data)
    {
        if($this->productMetadata->getVersion()<'2.2.0'){
            $result =  $this->serserialize($data);
        }else{
            $result =  $this->jsonserialize($data);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function unserialize($string)
    {
        if($this->productMetadata->getVersion()<'2.2.0'){
            $result =  $this->serunserialize($string);
        }else{
            $result =  $this->jsonunserialize($string);
        }
        return $result;
    }
    
    
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function jsonserialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function jsonunserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return $result;
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function serserialize($data)
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return serialize($data);
    }

    /**
     * {@inheritDoc}
     */
    public function serunserialize($string)
    {
        if (false === $string || null === $string || '' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        set_error_handler(
            function () {
                restore_error_handler();
                throw new \InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            },
            E_NOTICE
        );
        $result = unserialize($string, ['allowed_classes' => false]);
        restore_error_handler();
        return $result;
    }
    
    
}
