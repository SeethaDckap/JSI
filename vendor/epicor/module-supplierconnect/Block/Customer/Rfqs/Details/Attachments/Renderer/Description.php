<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Supplierconnect
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Attachments\Renderer;


use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Attachment description field renderer class.
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Registry class.
     *
     * @var Registry
     */
    protected $registry;


    /**
     * Constructor function
     *
     * @param Context  $context  Context class.
     * @param Registry $registry Registry class.
     * @param array    $data     Data.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data=[]
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()


    /**
     * Render function.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $key   = 'existing';
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        if ($this->registry->registry('rfq_editable') && $row->getWebFileId()) {
            $html = '<input type="text" name="attachments['.$key.']['.$row->getUniqueId().']['.$index.']" value="'.$value.'" class="attachments_'.$index.'"/>';
        } else {
            $html = $value;
        }

        return $html;

    }//end render()


}//end class
