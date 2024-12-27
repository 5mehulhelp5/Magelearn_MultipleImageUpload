define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, fileUploader) {
    return fileUploader.extend({
        getImageClass: function (file) {
            if ($('input[name="base_img"]').val() == file.name) {
                return 'file-uploader-preview image-uploader-preview mg-preview-image mg-base-img';
            }

            return 'file-uploader-preview image-uploader-preview mg-preview-image';
        },

        makeBase: function(file) {
            $('.mg-preview-image').each(function () {
                $(this).removeClass('mg-base-img');
            });
            $(event.target.closest('.mg-preview-image')).addClass('mg-base-img');
            $('input[name="base_img"]').val(file.name).change();
        },
    })
});
