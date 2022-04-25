<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details;


/**
 * Dealer Connect Dealer Quote Block Template
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Template extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_quotes_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_quotes_edit';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getRegistry($value)
    {
        return $this->registry->registry($value);
    }
}
