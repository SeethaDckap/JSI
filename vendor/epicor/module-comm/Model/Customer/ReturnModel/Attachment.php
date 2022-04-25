<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\ReturnModel;


/**
 * 
 * @method string getReturnId()
 * @method string getLineId()
 * @method string getAttachmentId()
 * 
 * @method setReturnId(int $value)
 * @method setLineId(int $value)
 * @method setAttachmentId(int $value)
 * 
 * Return attachment model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Attachment extends \Epicor\Database\Model\Customer\ReturnModel\Attachment
{

    protected $_eventPrefix = 'ecc_customer_return_attachment';
    protected $_eventObject = 'customer_return_attachment';
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment');
    }

}
