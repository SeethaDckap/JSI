<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Widget\Grid\Massaction;

/**
 * Grid widget massaction block
 *
 * @method \Magento\Quote\Model\Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @TODO MAGETWO-31510: Remove deprecated class
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Epicor_Common::widget/grid/massaction_extended.phtml';
    
    public function getCollection()
    {        
        parent::getCollection();
        $this->setTemplate('Epicor_Common::widget/grid/massaction_extended.phtml');

        return $this;
    }

    /**
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();

        if ($this->getMassactionIdField()) {
            $massActionIdField = $this->getMassactionIdField();
        } else {
            $massActionIdField = $this->getParentBlock()->getMassactionIdField();
        }
        $gridIds = [];
        if (!is_null($allIdsCollection->setPageSize(0)->getItems())) {
            $gridIds = $allIdsCollection->setPageSize(0)->getColumnValues($massActionIdField);
        }

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }

}
