/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
  "jquery",
], function ($) {
  'use strict';
  $(document).ready(function() {
    cartqtyplusminus();

    /**
     * Qty box plus minus
     */
    function cartqtyplusminus(){
      $('.product-add-form .box-tocart .control .qtyplus').on('click', function() {
        var input = $('.product-add-form .box-tocart .control .qty');
        var valplus;
        //var val = +input.val() + 1;
        if(+input.val() == 0 ){
          valplus = +input.val() + 1;
          input.val(valplus);
        }
        else{
          valplus = +input.val() > 0 ? +input.val() + 1 : 0;
          input.val(valplus);
        }
        return false;
      });
      $('.product-add-form .box-tocart .control .qtyminus').on('click', function(){
        var input = $('.product-add-form .box-tocart .control .qty');
        var valminus = +input.val() > 0 ? +input.val() - 1 : 0;
        input.val(valminus);
        return false;
      });
    }

  });
});