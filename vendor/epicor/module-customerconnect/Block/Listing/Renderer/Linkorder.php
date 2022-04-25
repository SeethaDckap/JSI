<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Listing\Renderer;


/**
 * Order link display
 *
 * @author Gareth.James
 */
class Linkorder extends \Epicor\Common\Block\Renderer\Encodedlinkabstract
{

    protected $_path = 'customerconnect/orders/details';
    protected $_pathTypes = [
        'O' => [
            'frontend_resource' => 'Epicor_Customerconnect::customerconnect_account_orders_details',
            'url' => 'customerconnect/orders/details',
            'message_type' => 'cuod',
            'message_base' => 'customerconnect'
        ],
        'R' => [
            'frontend_resource' => 'Epicor_Customerconnect::customerconnect_account_returns_details',
            'url' => 'customerconnect/returns/details',
            'message_type' => 'crrd',
            'message_base' => 'epicor_comm',

        ]
    ];
    protected $_key = 'order';
    protected $_addBackUrl = true;
    protected $_permissions = "Epicor_Customerconnect::customerconnect_account_orders_details";

    /**
     * @var \Epicor\Common\Model\MessageRequestModelReader
     */
    protected $messageRequestModelReader;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Model\MessageRequestModelReader $messageRequestModelReader,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = [])
    {
        $this->messageRequestModelReader = $messageRequestModelReader;
        parent::__construct(
            $context,
            $commonAccessHelper,
            $commHelper,
            $urlEncoder,
            $encryptor,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $type = 'O';

        $orderNumber = $row->getData($this->getColumn()->getIndex());
        if ($orderNumber instanceof \Magento\Framework\DataObject) {
            if ($orderNumber->hasData('_attributes')) {
                $attributes = $orderNumber->getData('_attributes');
                if (array_key_exists($attributes->getType(), $this->_pathTypes)) {
                    $type = $attributes->getType();
                }
            }
        }
        if (array_key_exists('message_base', $this->_pathTypes[$type])) {
            $message = $this->messageRequestModelReader->getModel($this->_pathTypes[$type]['message_base'],$this->_pathTypes[$type]['message_type']);
            $this->_showLink = $message->isActive();
        }

        $this->_path = $this->_pathTypes[$type]['url'];
        $this->_permissions = $this->_pathTypes[$type]['frontend_resource'];

        return parent::render($row);
    }

}
