<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ details page title
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Customerconnect\Block\Customer\Title
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $commonAccessHelper,
            $commReturnsHelper,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Returns whether an entity can be reordered or not
     *
     * @return boolean
     */
    public function canReorder()
    {
        return false;
    }

    /**
     * Returns whether an entity can be reordered or not
     *
     * @return boolean
     */
    public function canReturn()
    {
        return false;
    }

}
