<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Helper\Frontend;

/**
 * Helper for Lists on the Quick order pad
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Quickorderpad extends \Epicor\Lists\Helper\Frontend\Product
{
    /**
     * Looks for the Quick Order Pad List with highest priority
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getDefaultList()
    {
        return $this->getQuickOrderPadLists('default');
    }
    
    /**
     * Returns the List saved in session, if none returns default list
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getSessionList()
    {
        if (is_null($this->sessionList)) {
            $sessionHelper = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $listId = $sessionHelper->getValue('ecc_quickorderpad_list');
            if ($listId === false || is_null($listId)) {
                $defaultList = $this->getDefaultList();
                $listId = $defaultList ? $defaultList->getId() : $listId;
            }

            $this->setSessionList($listId);
        }

        return $this->sessionList;
    }
    
    /**
     * Gets an array of Quick Order Pad enabled Lists, ordered by Title
     *
     * @return array
     */
    public function getQuickOrderPadLists($return = 'all', $scope = null)
    {
        if ($this->quickOrderPadLists) {
            return $this->quickOrderPadLists[$return];
        }

        $qopLists = $this->registry->registry('qop_lists');

        if ($qopLists) {
            $this->quickOrderPadLists = $qopLists;
            return $this->quickOrderPadLists[$return];
        }

        $quickOrderPadLists = array(
            'all' => array(),
            'default' => false
        );

        $lists = $this->getActiveLists();
        foreach ($lists as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */

            // skip lists that are mandatory or contracts
            if ($list->hasSetting('M') || $list->getType() == 'Co') {
                continue;
            }

            $addList = true;
            $listProducts = $this->getProductIdsByList($list, true);

            $list->setProductCount(count($listProducts));

            if (count($listProducts) == 0 && $scope != "selector") {
                $addList = false;
            }

            $list->setTitle($list->getTitle());

            if ($addList) {
                $quickOrderPadLists['all'][$list->getTitle() . $list->getId()] = $list;
                $isDefault = $this->shouldListBeDefaultQop($list,
                                                           $quickOrderPadLists['default']);
                if ($isDefault) {
                    $quickOrderPadLists['default'] = $list;
                }
            }
        }

        ksort($quickOrderPadLists['all']);
        $this->registry->unregister('qop_lists');
        $this->registry->register('qop_lists',
                                  $quickOrderPadLists);
        $this->quickOrderPadLists = $quickOrderPadLists;

        return $this->quickOrderPadLists[$return];
    }

}
