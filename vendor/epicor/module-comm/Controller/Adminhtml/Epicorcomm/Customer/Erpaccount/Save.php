<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Save extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount {

    public function execute() {

        if ($data = (array) $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->commCustomerErpaccountFactory->create();
            if ($id) {
                $model->load($id);
            }
            $data['is_budget_active'] = isset($data['is_budget_active']) ? 1 : 0;
            $data['name'] = $model->getName();
            $model->addData($data);
            $this->backendSession->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $customAddressAllowed = $this->getRequest()->getParam('custom_address_allowed');
                $updateValue = $customAddressAllowed == '' ? null : $customAddressAllowed;
                $model->setCustomAddressAllowed($updateValue);
                if (isset($data['allow_masquerade'])) {
                    $allowed = $data['allow_masquerade'] == '' ? null : $data['allow_masquerade'];
                    $model->setAllowMasquerade($allowed);
                }

                if (isset($data['allow_masquerade_cart_clear'])) {
                    $allowed = $data['allow_masquerade_cart_clear'] == '' ? null : $data['allow_masquerade_cart_clear'];
                    $model->setAllowMasqueradeCartClear($allowed);
                }

                if (isset($data['allow_masquerade_cart_reprice'])) {
                    $allowed = $data['allow_masquerade_cart_reprice'] == '' ? null : $data['allow_masquerade_cart_reprice'];
                    $model->setAllowMasqueradeCartReprice($allowed);
                }

                if (isset($data['newparent']) && isset($data['newparent']['account']) && !empty($data['newparent']['account'])) {
                    $model->addParent($data['newparent']['account'], $data['newparent']['type']);
                }

                if (isset($data['newchild']) && isset($data['newchild']['account']) && !empty($data['newchild']['account'])) {
                    $model->addChild($data['newchild']['account'], $data['newchild']['type'], true);
                }

                if (isset($data['deleted_parents']) && !empty($data['deleted_parents'])) {
                    foreach ($data['deleted_parents'] as $type) {
                        $model->removeParentByType($type);
                    }
                }

                if (isset($data['deleted_children']) && !empty($data['deleted_children'])) {
                    foreach ($data['deleted_children'] as $child) {
                        $data = unserialize(base64_decode($child));
                        $model->removeChild($data['id'], $data['type']);
                    }
                }


                if (isset($data['is_warranty_customer'])) {
                    $model->setIsWarrantyCustomer(true);
                } else {
                    $model->setIsWarrantyCustomer(false);
                }

                if (isset($data['allow_backorders'])) {
                    $model->setAllowBackorders(true);
                } else {
                    $model->setAllowBackorders(false);
                }

                if (isset($data['cpn_editing'])) {
                    if ($data['cpn_editing'] == '') {
                        $model->setCpnEditing(null);
                    } else {
                        $model->setCpnEditing($data['cpn_editing']);
                    }
                }

                if (isset($data['po_mandatory'])) {
                    if ($data['po_mandatory'] == '') {
                        $model->setPoMandatory(null);
                    } else {
                        $model->setPoMandatory($data['po_mandatory']);
                    }
                }

                if (isset($data['is_branch_pickup_allowed'])) {
                    if ($data['is_branch_pickup_allowed'] == '') {
                        $model->setIsBranchPickupAllowed(null);
                    } else {
                        $model->setIsBranchPickupAllowed($data['is_branch_pickup_allowed']);
                    }
                }

                if (isset($data['is_tax_exempt'])) {
                    if ($data['is_tax_exempt'] == '') {
                        $model->setIsTaxExempt(null);
                    } else {
                        $model->setIsTaxExempt($data['is_tax_exempt']);
                    }
                }

                if (isset($data['hide_price_options'])) {
                    if ($data['hide_price_options'] == '') {
                        $model->setHidePriceOptions(null);
                    } else {
                        $model->setHidePriceOptions($data['hide_price_options']);
                    }
                }

                if (isset($data['erp_access_rights'])) {
                    if ($data['erp_access_rights'] == '') {
                        $model->setErpAccessRights(2);
                    } else {
                        $model->setErpAccessRights($data['erp_access_rights']);
                    }
                }

                $accessRoles = isset($data['erp_access_roles']) ? $data['erp_access_roles'] : [];
                $this->saveAccessRoles($accessRoles, $id);
                
                if (isset($data['is_invoice_edit'])) {
                    if ($data['is_invoice_edit'] == '') {
                        $model->setIsInvoiceEdit(null);
                    } else {
                        $model->setIsInvoiceEdit($data['is_invoice_edit']);
                    }
                }

                if (isset($data['is_arpayments_allowed'])) {
                    if ($data['is_arpayments_allowed'] == '') {
                        $model->setIsArpaymentsAllowed(null);
                    } else {
                        $model->setIsArpaymentsAllowed($data['is_arpayments_allowed']);
                    }
                }
                if (isset($data['sou_invoice_options'])) {
                    if ($data['sou_invoice_options'] == '') {
                        $model->setSouInvoiceOptions(null);
                    } else {
                        $model->setSouInvoiceOptions($data['sou_invoice_options']);
                    }
                }

                if (isset($data['sou_shipment_options'])) {
                    if ($data['sou_shipment_options'] == '') {
                        $model->setSouShipmentOptions(null);
                    } else {
                        $model->setSouShipmentOptions($data['sou_shipment_options']);
                    }
                }


                if (isset($data['set_warranty_allowed'])) {
                    if ($data['set_warranty_allowed'] == '') {
                        $model->setWarrantyConfig(null);
                    } else {
                        $model->setWarrantyConfig($data['set_warranty_allowed']);
                    }
                }

                //process Contracts if lists enabled
                if ($this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $this->processContracts($model, $data);
                }

                //default location
                $erp_default_location = $this->getRequest()->getParam('erp_default_location');
                $model->setDefaultLocationCode($erp_default_location);

                $saveLocations = $this->getRequest()->getParam('in_location');
                if (!is_null($saveLocations)) {
                    $this->saveLocations($model, $data);
                }
                $links = $this->getRequest()->getPost('links');
                if (!is_null($links)) {
                    $this->saveLists($model, $data);
                    $this->saveMasterShoppers($model, $data);
                }
                if (isset($links['payments'])) {
                    $this->savePaymentMethods($model, $data);
                }
                if (isset($links['delivery'])) {
                    $this->saveDeliveryMethods($model, $data);
                }
                if (isset($links['shipstatus'])) {
                    $this->saveShipStatus($model, $data);
                }
                $model->save();

                $saveCustomer = $this->getRequest()->getParam('in_customer');
                if (!is_null($saveCustomer)) {
                    $this->saveCustomers($model, $data);
                }

                $saveStores = $this->getRequest()->getParam('selected_store');
                if (!is_null($saveStores)) {
                    $this->saveStores($model, $data);
                }

                $saveSalesRep = $this->getRequest()->getParam('selected_salesreps');
                if (!is_null($saveSalesRep)) {
                    $this->saveSalesReps($model, $data);
                }

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Erp Account'));
                }

                $this->messageManager->addSuccessMessage(__('ERP Account was successfully saved.'));
                $this->backendSession->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->backendSession->setFormData(false);
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
        $this->_redirect('*/*/');
    }

    protected function saveAccessRoles($accessRoles, $id) {
        $accessRoleErpAccount = $this->accessroleErpAccountFactory->create();
        $accessRoleErpAccountCollection = $accessRoleErpAccount->getCollection()
                ->addFieldToFilter('erp_account_id', $id)
                ->addFieldToFilter('by_erp_account', 1)
                ->getData();
        if ($accessRoleErpAccountCollection) {
            foreach ($accessRoleErpAccountCollection as $val) {
                $rolesIds [] = $val['access_role_id'];
            }

            $result = array_diff($rolesIds, $accessRoles);
            if (empty($accessRoles) && count($accessRoleErpAccountCollection) == 1) {
                if ($accessRoleErpAccountCollection[0]['by_role'] == 1) {
                    $accessRoleErpAccount->load($accessRoleErpAccountCollection[0]['id'])->setByErpAccount(0);
                    $accessRoleErpAccount->save();
                } else {
                    $accessRoleErpAccount->load($accessRoleErpAccountCollection[0]['id'])->delete();
                }
            } else if ($result) {
                foreach ($result as $val) {
                    $accessRoleErpAccountCollection = $accessRoleErpAccount->getCollection()
                            ->addFieldToFilter('erp_account_id', $id)
                            ->addFieldToFilter('by_erp_account', 1)
                            ->addFieldToFilter('access_role_id', $val)
                            ->getData();
                    if ($accessRoleErpAccountCollection[0]['by_role'] == 1) {
                        $accessRoleErpAccount->load($accessRoleErpAccountCollection[0]['id'])->setByErpAccount(0);
                        $accessRoleErpAccount->save();
                    } else {
                        $accessRoleErpAccount->load($accessRoleErpAccountCollection[0]['id'])->delete();
                    }
                }
            }
        }

        foreach ($accessRoles as $value) {
            $collection = $accessRoleErpAccount->getCollection()
                    ->addFieldToFilter('erp_account_id', $id)
                    ->addFieldToFilter('access_role_id', $value)
                    ->getData();
            $accessRoleErpAccount = $this->accessroleErpAccountFactory->create();
            if (empty($collection) || $collection[0]['access_role_id'] != $value) {
                $accessRoleErpAccount->setAccessRoleId($value);
                $accessRoleErpAccount->setErpAccountId($id);
                $accessRoleErpAccount->setByErpAccount(1);
                $accessRoleErpAccount->setByRole(0);
                $accessRoleErpAccount->save();
            } else if ($collection[0]['access_role_id'] == $value && $collection[0]['by_erp_account'] == 0 && $collection[0]['by_role'] == 1) {
                $accessRoleErpAccount->load($collection[0]['id']);
                $accessRoleErpAccount->setByErpAccount(1);
                $accessRoleErpAccount->save();
            }
        }
    }

}
