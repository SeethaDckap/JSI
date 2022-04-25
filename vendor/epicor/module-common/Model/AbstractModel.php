<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


class AbstractModel extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'epicor_common_abstract';
    protected $_eventObject = 'object';

    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->getCreatedAt()) {
            //M1 > M2 Translation Begin (Rule 25)
            //$this->setCreatedAt(now());
            $this->setCreatedAt(date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
        }

        if ($this->hasDataChanges()) {
            //M1 > M2 Translation Begin (Rule 25)
            //$this->setUpdatedAt(now());
            $this->setUpdatedAt(date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
        }
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 11) == 'checkAndSet') {
            $key = $this->_underscore(substr($method, 11));
            return $this->checkAndSet($key, isset($args[0]) ? $args[0] : null);
        } else {
            return parent::__call($method, $args);
        }
    }

    public function checkAndSet($key, $value)
    {
        $existingValue = $this->getData($key);
        if ($existingValue != $value) {
            $this->setData($key, $value);
        }
        return $this;
    }

}
