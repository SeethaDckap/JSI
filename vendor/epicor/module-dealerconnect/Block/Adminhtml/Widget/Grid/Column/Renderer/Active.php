<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Widget\Grid\Column\Renderer;


/**
 * Active column renderer
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Active extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        array $data = []
    ) {
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render active grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $active = $row->getData($this->getColumn()->getIndex());

        if ($active) {
            $startDate = $row->getData($this->getColumn()->getStartDate());
            $endDate = $row->getData($this->getColumn()->getEndDate());

            $dateModel = $this->dateTimeDateTimeFactory->create();
            $currentTimeStamp = $dateModel->timestamp(time());
            $startTimeStamp = $dateModel->timestamp(strtotime($startDate));
            $endTimeStamp = $dateModel->timestamp(strtotime($endDate));

            if ($endDate && $endTimeStamp < $currentTimeStamp) {
                $status = __('Ended');
            } else if ($startDate && $startTimeStamp > $currentTimeStamp) {
                $status = __('Pending');
            } else {
                $status = __('Active');
            }
        } else {
            $status = __('Disabled');
        }

        return $status;
    }

}
