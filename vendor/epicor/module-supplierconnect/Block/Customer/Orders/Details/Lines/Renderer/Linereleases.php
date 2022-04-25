<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer;


/**
 * Line releases display
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Linereleases extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;


    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->registry = $registry;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $html = '';
        $orderDisplay = $this->registry->registry('supplier_connect_order_display');

        $releases = ($row->getReleases()) ? $row->getReleases()->getasarrayRelease() : array();

        if (count($releases) > 0) {

            $html = '</td></tr><tr id="row-releases-' . $row->getId() . '" style="display: none;"><td colspan="11">
            <table class="expand-table">
                <thead>
                    <tr class="headings">
                        <th>' . __('Releases') . '</th>
                        <th>' . __('Release') . '</th>
                        <th>' . __('Due Date') . '</th>
                        <th>' . __('Promise Date') . '</th>
                        <th>' . __('Ordered Qty') . '</th>
                        <th>' . __('Received Qty') . '</th>
                        <th>' . __('Request Changes') . '</th>
                        <th>' . __('New Date') . '</th>
                        <th>' . __('New Promise Date') . '</th>
                        <th>' . __('New Qty') . '</th>
                        <th>' . __('Comment') . '</th>
                    </tr>
                </thead>
                <tbody>
            ';
            $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            foreach ($releases as $release) {
                $helper = $this->supplierconnectHelper;

                $number = $release['_attributes']->getNumber();
                $changed = $release['changed'];
                $quantity = $release['changed_quantity'] > 0 ? $release['changed_quantity'] : $release['quantity'];
                $comment = $release['comment'];
                $date = $release['changed_due_date'] ?
                    $this->supplierconnectHelper->getLocalDate(
                        $release['changed_due_date'], \IntlDateFormatter::SHORT
                    ) : '';

                $promiseDate = $release['changed_promise_date'] ?
                    $this->supplierconnectHelper->getLocalDate(
                        $release['promise_date'], \IntlDateFormatter::SHORT
                    ) : '';

                if ($orderDisplay == 'edit' && $changed == 'true') {
                    $fieldsDisabled = 'disabled="disabled"';

                    $changedField = '<input 
                    name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed]"
                     type="checkbox" class="purchase_order_changed"/>';
                    $dueDateField = '<input id="line_' . $row->getId() . '_release_' . $number . '_date" 
                    name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_due_date]" 
                    type="text" value="' . $date . '" ' . $fieldsDisabled . '/>
                        
                        <script type="text/javascript">// <![CDATA[
                        require([
                            "jquery",
                            "mage/calendar"
                        ], function(jQuery) {
                          jQuery(\'#line_' . $row->getId() . '_release_' . $number . '_date\').calendar();
                        })
                // ]]></script>';

                    $promiseDateField = '<input id="line_' . $row->getId() . '_release_' . $number . '_promise_date" 
                    name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_promise_date]" 
                    type="text" value="' . $date . '" ' . $fieldsDisabled . '/>
                        
                        <script type="text/javascript">// <![CDATA[
                        require([
                            "jquery",
                            "mage/calendar"
                        ], function(jQuery) {
                          jQuery(\'#line_' . $row->getId() . '_release_' . $number . '_promise_date\').calendar();
                        })
                // ]]></script>';
                    $quantityField = '<input 
                    name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_quantity]"
                     type="text" value="' . $quantity . '" ' . $fieldsDisabled . '/>';
                    $commentField = '<textarea 
                    name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][comment]" ' . $fieldsDisabled . '>' . $comment . '</textarea>';
                } else {
                    $changedField = '<input name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed]" 
                        type="hidden" class="purchase_order_changed" value="' . $changed . '"/>';
                    $dueDateField = $date . '<input id="line_' . $row->getId() . '_release_' . $number . '_date" 
                        name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_due_date]" type="hidden" value="' . $date . '"/>';
                    $promiseDateField = $date . '<input id="line_' . $row->getId() . '_release_' . $number . '_promise_date" 
                        name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_promise_date]" type="hidden" value="' . $date . '"/>';
                    $quantityField = $quantity . '<input name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][changed_quantity]" 
                        type="hidden" value="' . $quantity . '" />';
                    $commentField = $comment . '<input type="hidden" 
                        name="purchase_order[lines][' . $row->getUniqueId() . '][releases][' . $number . '][comment]" value="' . $comment . '"/>';
                }

                $html .= '
                  <tr>
                    <td></td>
                    <td>' . $number . '</td>
                    <td>' . ($release['due_date'] ? $this->supplierconnectHelper
                        ->getLocalDate($release['due_date'], \IntlDateFormatter::SHORT) : __('N/A')) . '</td>
                    
                    <td>' . ($release['promise_date'] ? $this->supplierconnectHelper
                        ->getLocalDate($release['promise_date'], \IntlDateFormatter::SHORT) : __('N/A')) . '</td>
                    <td>' . $release['quantity'] . '</td>
                    <td>' . $release['received_quantity'] . '</td>
                    <td>' . $changedField . '</td>
                    <td>' . $dueDateField . '</td>
                    <td>' . $promiseDateField . '</td>
                    <td>' . $quantityField . '</td>
                    <td>' . $commentField . '</td>
                  </tr>
                    ';
            }
            $html .= '</tbody></table>';
        }

        return $html;
    }

}
