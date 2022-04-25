<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Restrictionsessionset extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data['linkTypeValue']) {
            $selectedRestricionType = $data['linkTypeValue'];
            $this->backendAuthSession->setRestrictionTypeValue($selectedRestricionType);
        }
    }

    }
