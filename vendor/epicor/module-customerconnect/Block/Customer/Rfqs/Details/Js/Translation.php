<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Js;


/**
 * RFQ details js block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Translation extends \Epicor\Common\Block\Js\Translation
{

    protected function _construct()
    {
        parent::_construct();

        $translations = array(
            /* couldn't find these in a js file, but where there before so I kept them */
            'You must supply at least one Contact' => __('You must supply at least one Contact'),
            'You must supply at least one Line' => __('You must supply at least one Line'),
            'One or more lines require configuration, please see lines with a "Configure" link' => __('One or more lines require configuration, please see lines with a "Configure" link'),
            'Are you sure you want to delete?' => __('Are you sure you want to delete?'),
            'That function is not available' => __('That function is not available'),
            'Please enter a qty' => __('Please enter a qty'),
            'Line added successfully' => __('Line added successfully'),
            'Not currently available' => __('Not currently available'),
            /* skin/frontend/base/default/epicor/common/js/common.js */
            'Error occured in Ajax Call' => __('Error occured in Ajax Call'),
            'No records found.' => __('No records found.'),
            /* skin/frontend/base/default/epicor/comm/js/quickadd.js */
            'Error occured retrieving additional data' => __('Error occured retrieving additional data'),
            /* skin/frontend/base/default/epicor/customerconnect/js/rfq/details/contacts.js */
            'Are you sure you want to delete selected contact?' => __('Are you sure you want to delete selected contact?'),
            'No Contacts Available' => __('No Contacts Available'),
            /* skin/frontend/base/default/epicor/customerconnect/js/rfq/details/core.js */
            'There are unsaved changes to this quote. These changes will be lost. Are you sure you wish to continue?' => __('There are unsaved changes to this quote. These changes will be lost. Are you sure you wish to continue?'),
            'One or more options is incorrect, please see page for details' => __('One or more options is incorrect, please see page for details'),
            'Are you sure you want to delete selected line?' => __('Are you sure you want to delete selected line?'),
            /* skin/frontend/base/default/epicor/customerconnect/js/rfq/details/lines.js */
            'Please select one or more lines' => __('Please select one or more lines'),
            'One or more lines had errors:' => __('One or more lines had errors:'),
            'SKU' => __('SKU'),
            'Does not exist - Select Custom Part' => __('Does not exist - Select Custom Part'),
            'Does not exist' => __('Does not exist'),
            'Lines added successfully' => __('Lines added successfully'),
            'One or more products require configuration. Please click on each "Configure" link in the lines list' => __('One or more products require configuration. Please click on each "Configure" link in the lines list'),
            'You must provide an SKU for all non-custom parts' => __('You must provide an SKU for all non-custom parts'),
            'You must provide a name for all custom parts' => __('You must provide a name for all custom parts'),
            'All quantities must be valid' => __('All quantities must be valid'),
            'Configure' => __('Configure'),
            'Edit Configuration' => __('Edit Configuration'),
            'No lines added' => __('No lines added'),
            'Line(s) added successfully' => __('Line(s) added successfully'),
            'Are you sure you want to delete selected lines?' => __('Are you sure you want to delete selected lines?'),
            'Are you sure you want to clone selected lines?' => __('Are you sure you want to clone selected lines?'),
            'TBA' => __('TBA'),
            /* skin/frontend/base/default/epicor/customerconnect/js/rfq/details/salesreps.js */
            'Are you sure you want to delete selected sales rep?' => __('Are you sure you want to delete selected sales rep?'),
            /* skin/frontend/base/default/epicor/salesrep/js/salesrepPricing.js */
            'The price entered was too low' => __('The price entered was too low'),
            'The discount entered was too high' => __('The discount entered was too high'),
            /* skin/frontend/base/default/epicor/salesrep/js/rfq-extra.js */
            'Revert to Web Price' => __('Revert to Web Price'),
        );

        $this->setTranslations($translations);
    }

}
