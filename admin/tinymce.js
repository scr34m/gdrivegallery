'use strict';
jQuery(document).ready(function ($) {
    $('#gdg-tinymce-button').click(tinymceOnclick);

    function tinymceOnclick() {
        if ($('#sgdg-tinymce-insert').attr('disabled')) {
            return;
        }
        tinymce.activeEditor.insertContent('[dgdg path="' + prompt('Google Drive gallery shared URL:') + '"]');
    }
});