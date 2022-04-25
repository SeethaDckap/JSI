<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Listing\Renderer;

class Additionalreference extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Epicor\Common\Helper\Xml $commonXmlHelper, \Epicor\Comm\Helper\Data $commHelper, array $data = []
    ) {
        $this->commonXmlHelper = $commonXmlHelper;
        $this->commHelper = $commHelper;
        parent::__construct(
                $context, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        $visible = $this->commHelper->isEccAdditionalReference();
        return ($visible) ? $row->getAdditionalReference() : null;
    }

}
