/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
/**
 * check empty attachment validation
 * @returns {boolean}
 */
function attachmentUploaded($attachmentTable) {
    var upload = true;
    jQuery('#' + $attachmentTable).each(function () {
        const fileElement = jQuery(this).find("td.newattachment").find(":file");
        if (fileElement.length > 0) {
            const fileName = fileElement.prop('files')[0];
            if (fileName == undefined) {
                upload = false;
                return false;
            }
        }
    });
    return upload;
}