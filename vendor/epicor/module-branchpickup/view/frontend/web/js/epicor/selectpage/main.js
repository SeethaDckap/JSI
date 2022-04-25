require([
  "jquery",
  'Epicor_BranchPickup/js/epicor/view/branch-select-page',
  'jquery/ui'
], 

function($,selectPage,fullScreenLoader) {
    //$(document).ready(function(){
        //$('#selectBranchlink').each(function() {
             $('.selectBranchlink').live('click',function() {
                var LocationCode = $(this).attr('data-custom');
                var LocationId =  $(this).attr('data-customid');
                if($('#errorBranchlink_'+LocationId).length > 0) {
                  selectPage.showEditLocationPopup(LocationCode);     
                } else {
                  selectPage.selectBranchPickupAddress(LocationCode);  
                }
                return false;
            });

            $('.remove_branch').live('click',function() {
                selectPage.removeBranchPickupAddress();
                return false;
            });
        //});
    //});    

});