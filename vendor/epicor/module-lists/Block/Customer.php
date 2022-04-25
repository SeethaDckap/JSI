<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block;


use Epicor\Lists\Helper\Frontend;
use \Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Lists customer Block
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Customer extends AbstractBlock implements IdentityInterface
{
    /**
     * @var Frontend
     */
    private $frontendHelper;

    public function __construct(
        Context $context,
        Frontend $frontendHelper,
        array $data = []
    ) {
        $this->frontendHelper = $frontendHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get Identities
     *
     * @return array|string[]
     */
    public function getIdentities()
    {
        $listTags = $this->frontendHelper->getEscapedActiveLists();
        $listTags = explode(",", $listTags);
        return $listTags;
    }

}
