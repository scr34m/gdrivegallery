<?php

namespace GoogleDriveGallery\Admin\TinyMCE;

if (!is_admin()) {
    return;
}

/**
 * Registers all the hooks for the TinyMCE plugin and the "list_gallery_dir" AJAX endpoint
 */
function register()
{
    add_action('media_buttons', '\\GoogleDriveGallery\\Admin\\TinyMCE\\add');
    add_action('wp_enqueue_media', '\\GoogleDriveGallery\\Admin\\TinyMCE\\register_scripts_styles');
    add_action('wp_ajax_list_gallery_dir', '\\GoogleDriveGallery\\Admin\\TinyMCE\\handle_ajax');
}

/**
 * Adds the Google Drive gallery button to TinyMCE and enables the use of ThickBox
 */
function add()
{
    if ((!current_user_can('edit_posts') && !current_user_can('edit_pages')) || 'true' !== get_user_option(
            'rich_editing'
        )) {
        return;
    }
    echo('<a href="#" id="gdg-tinymce-button" class="button"><img class="gdg-tinymce-button-icon" src="' . esc_attr(
            plugins_url('/deeb-google-drive-gallery/admin/icon.png')
        ) . '">' . esc_html__('Google Drive gallery', 'deeb-google-drive-gallery') . '</a>');
}

/**
 * Enqueues the scripts and styles used by the Tiny MCE plugin.
 */
function register_scripts_styles()
{
    if ((!current_user_can('edit_posts') && !current_user_can('edit_pages')) || 'true' !== get_user_option(
            'rich_editing'
        )) {
        return;
    }
    \GoogleDriveGallery\enqueue_style('gdg_tinymce', '/admin/tinymce.css');
    \GoogleDriveGallery\enqueue_script('gdg_tinymce', '/admin/tinymce.js');
    wp_localize_script(
        'gdg_tinymce',
        'gdgTinymceLocalize',
        [
            'dialog_title' => esc_html__('Google Drive gallery', 'deeb-google-drive-gallery'),
            'root_name' => esc_html__('Google Drive gallery', 'deeb-google-drive-gallery'),
            'insert_button' => esc_html__('Insert', 'deeb-google-drive-gallery'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gdg_editor_plugin'),
        ]
    );
}

