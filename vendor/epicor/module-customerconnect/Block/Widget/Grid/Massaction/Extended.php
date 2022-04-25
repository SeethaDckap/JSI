<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Widget\Grid\Massaction;

use Magento\Backend\Block\Widget\Grid\Massaction\Extended as ExtendedMassaction;

/**
 * Class Extended
 * @package Epicor\Customerconnect\Block\Widget\Grid\Massaction
 */
class Extended extends ExtendedMassaction
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Epicor_Customerconnect::widget/grid/massaction_extended.phtml';

    /**
     * @return $this
     */
    public function getCollection()
    {        
        parent::getCollection();
        $this->setTemplate('Epicor_Customerconnect::widget/grid/massaction_extended.phtml');

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
