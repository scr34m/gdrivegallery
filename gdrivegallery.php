<?php
/*
Plugin Name:	Google Drive gallery
Description:	A simple gallery linking to google drive shared gallery
Version:	    1.0.0
Author:         Gábor Győrvári
Author URI:     http://github.com/scr34m
License:	    MIT
Text Domain:	deeb-google-drive-gallery
*/

namespace GoogleDriveGallery;


require_once __DIR__ . '/frontend/shortcode.php';
require_once __DIR__ . '/admin/tinymce.php';

function init()
{
    \GoogleDriveGallery\Frontend\Shortcode\register();
    \GoogleDriveGallery\Admin\TinyMCE\register();
}

function register_script($handle, $src, $deps = [])
{
    wp_register_script(
        $handle,
        plugins_url('/deeb-google-drive-gallery' . $src),
        $deps,
        filemtime(WP_PLUGIN_DIR . '/deeb-google-drive-gallery' . $src),
        true
    );
}

function register_style($handle, $src, $deps = [])
{
    wp_register_style(
        $handle,
        plugins_url('/deeb-google-drive-gallery' . $src),
        $deps,
        filemtime(WP_PLUGIN_DIR . '/deeb-google-drive-gallery' . $src)
    );
}

function enqueue_script($handle, $src, $deps = [])
{
    register_script($handle, $src, $deps);
    wp_enqueue_script($handle);
}

function enqueue_style($handle, $src, $deps = [])
{
    register_style($handle, $src, $deps);
    wp_enqueue_style($handle);
}

init();
