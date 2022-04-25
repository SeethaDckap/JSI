/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


var listAddress;
function initListAddress(form, table){
    listAddress = new Epicor_Comm.childrenGrid;
    listAddress.parentIdField = 'list_id';
    listAddress.fieldsMap = {
        id:     'address_id',
        name:   'address_name'
    };
    listAddress.initialize(form,table);
    listAddress.close();
}
