<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class Linesearch extends \Epicor\Customerconnect\Controller\Rfqs\Linesearch
{
    public function execute()
    {
        foreach ($this->getRequest()->getParams() as $key => $value) {
            if (substr($key, 0, 4) == 'amp;')
                $this->getRequest()->setParam(substr($key, 4), $value);
        }

        $q = $this->getRequest()->getParam('q', '');
        $instock = $this->getRequest()->getParam('instock', '');
        $this->registry->register('search-query', $q);

        if (!empty($q)) {

//        Mage::register('search-sku', $sku);
            $this->registry->register('search-instock', $instock != '' ? true : false);

            /** @var \Magento\Search\Model\Query $query */
            $query = $this->queryFactory->get();

            $query->setStoreId($this->storeManager->getStore()->getId());
            if ($this->catalogSearchHelper->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                } else {
                    $query->setPopularity(1);
                }

                $query->prepare();
            }

            $this->catalogSearchHelper->checkNotes();

            if (!empty($q) && !$this->catalogSearchHelper->isMinQueryLength()) {
                $query->save();
            }
        }
        if ($query->getNumResults() == 0 && is_null($this->getRequest()->getParam('custom_part'))) {
            $return_data = [
                'result' => __("Part not found.")
            ];
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData(json_encode($return_data));
            return $resultJson;
        } else {
            return $this->resultLayoutFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);
        }
    }

}
