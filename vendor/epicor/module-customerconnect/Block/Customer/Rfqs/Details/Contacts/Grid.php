<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Contacts;


class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    private $_contactCodes = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('rfq_contacts');
        $this->setDefaultSort('product_code');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqd');
        $this->setIdColumn('product_code');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $rfq = $this->registry->registry('customer_connect_rfq_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if ($rfq) {
            $contactsData = ($rfq->getContacts()) ? $rfq->getContacts()->getasarrayContact() : array();

            $contacts = array();

            // add a unique id so we have a html array key for these things
            foreach ($contactsData as $row) {
                $row->setUniqueId(uniqid());
                $this->_contactCodes[] = $row->getNumber();
                $contacts[] = $row;
            }

            $this->setCustomData($contacts);
        }
    }

    protected function _getColumns()
    {

        $columns = array();

        $columns['delete'] = array(
            'header' => __('Delete'),
            'align' => 'center',
            'index' => 'delete',
            'type' => 'text',
            'width' => '50px',
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Details\Contacts\Renderer\Delete',
            'filter' => false,
            'column_css_class' => $this->registry->registry('rfqs_editable') ? '' : 'no-display',
            'header_css_class' => $this->registry->registry('rfqs_editable') ? 'contact-delete-header' : 'no-display',
        );

        $columns['name'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'type' => 'text',
            'filter' => false
        );

        return $columns;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();

        $helper = $this->commHelper;

        $erpAccount = $helper->getErpAccountInfo();

        $customerOptions = '';

        foreach ($erpAccount->getCustomers($erpAccount->getId()) as $customer) {
            /* @var $customer \Epicor\Comm\Model\Customer */
            $customer->load($customer->getId());
            $code = $customer->getEccContactCode();

            if (!empty($code) && !in_array($code, $this->_contactCodes) || $this->registry->registry('rfq_new')) {

                if ($customer->getEccContactCode()) {
                    $details = array(
                        'number' => $customer->getEccContactCode(),
                        'name' => $customer->getName()
                    );

                    $customerOptions .= '<option value="' . base64_encode(serialize($details)) . '">'
                        . $customer->getName()
                        . '</option>';
                }
            }
        }

        $html .= '<div style="display:none">
            <table>
                <tr title="" class="contacts_row" id="contacts_row_template">
                    <td class="a-center">
                        <input type="checkbox" name="contacts[][delete]" class="contacts_delete"/>
                    </td>
                    <td class="a-left last">
                        <select name="contacts[][details]" class="contacts_details" >
                        ' . $customerOptions . '
                        </select>
                    </td>
                </tr>
            </table>
        </div>';




        $html .= '</script>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        $extra = $this->registry->registry('rfq_new') ? ' new' : '';
        return 'contacts_row' . $extra;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
