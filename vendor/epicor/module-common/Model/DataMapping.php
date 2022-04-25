<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;

use Magento\Framework\Model\AbstractModel;

class DataMapping extends AbstractModel
{

    /**
     * Model Class for Data Mapping
     *
     * @category   Epicor
     * @author     Epicor Websales Team
     *
     * @method string getCreatedAt()
     * @method string getMessage()
     * @method string getOrignalTag()
     * @method string getMappedTag()
     * @method string getStoreId()
     *
     * @method string setCreatedAt()
     * @method string setMessage()
     * @method string setOrignalTag()
     * @method string setMappedTag()
     * @method string setStoreId()
     *
     */

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Epicor\Common\Model\ResourceModel\DataMapping');
    }

    public function getByType($messageType=null)
    {
        $collection = $this->getResourceCollection();
        if($messageType) {
            $collection->addFieldtoFilter('message', $messageType);
        }
        $collection->setOrder('id');
        return $collection->getItems();
    }

}
