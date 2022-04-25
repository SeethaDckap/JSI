<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Syn;


/**
 * Syn log types renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Types extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;
    /*
     * @var \Magento\Framework\Unserialize\Unserialize $unserialize,    
     */
    protected $serialize;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Framework\Unserialize\Unserialize $unserialize,    
        array $data = []
    ) {
        $this->commEntityregHelper = $commEntityregHelper;
        $this->unserialize = $unserialize;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {   
        $data = $row->getData($this->getColumn()->getIndex());

        if (!empty($data)) {
            $value = $this->unserialize($data);
            $html = implode(', ', $value);
        } else {
            $html = '';
        }
        
        $helper = $this->commEntityregHelper;
        /* @var $helper Epicor_Comm_Helper_Entityreg */

        $typeDescs = $helper->getRegistryTypeDescriptions($this->unserialize($data));

        $search = array_keys($typeDescs);
        $replace = array_values($typeDescs);

        $html = str_replace($search, $replace, $html);

        return $html;
    }

    
    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        if (false === $string || null === $string || '' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
       try {
        $result = unserialize($string, ['allowed_classes' => false]);
       } catch (\Exception $e) {     
           $result = [];
           // throw new \InvalidArgumentException($string.'-Unable to unserialize value.'.$e->getMessage());
       }
        return $result;
    }
    
}