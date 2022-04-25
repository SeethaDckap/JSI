<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract\Renderer;


/**
 * Status column renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Addressactive extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    ) {
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render active grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        return $this->listsHelper->getAddressStatus($row);
    }

}
