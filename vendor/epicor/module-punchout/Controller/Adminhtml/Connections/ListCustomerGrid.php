<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

declare(strict_types=1);

namespace Epicor\Punchout\Controller\Adminhtml\Connections;

use \Epicor\Punchout\Controller\Adminhtml\Connections;

/**
 * Manage Connections
 */
class ListCustomerGrid extends Connections
{


    /**
     * Render customer grid listing.
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        if ($this->getRequest()->getParam('grid')) {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock(
                    'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Customer\Grid'
                )->toHtml()
            );
        } else {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock(
                    'Epicor\Punchout\Block\Adminhtml\Connections\Edit\Form\Customer'
                )->toHtml()
            );
        }

    }//end execute()


}//end class
