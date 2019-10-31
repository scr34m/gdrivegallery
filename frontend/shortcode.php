<?php

namespace GoogleDriveGallery\Frontend\Shortcode;

function register()
{
    add_action('init', '\\GoogleDriveGallery\\Frontend\\Shortcode\\add');
    add_action('wp_enqueue_scripts', '\\GoogleDriveGallery\\Frontend\\Shortcode\\register_scripts_styles');
}

function add()
{
    add_shortcode('dgdg', '\\GoogleDriveGallery\\Frontend\\Shortcode\\render');
}

function register_scripts_styles()
{
    \GoogleDriveGallery\register_style('dgdg_gallery_css', '/frontend/shortcode.css');
}

function render($atts = [])
{
    $path = '';
    $limit = 6;
    $dimension = 'h187';
    $images = '';
    extract($atts);

    if ($images) {
        $images = explode(',', $images);
        $limit = count($images);
    } else {
        $images = false;
    }

    return html($path, $limit, $dimension, $images);
}

function html($url, $limit, $dimension, $selected_images)
{
    wp_enqueue_style('dgdg_gallery_css');

    $nonce = hash('sha256', $url . $limit . serialize($selected_images));

    if (strstr($url, 'photos.app.goo.gl') !== false || strstr($url, 'photos.google.com') !== false) {
        $drive = false;
    } else {
        $drive = true;
    }

    if (false === ($value = get_transient('dgdg_nonce_' . $nonce))) {
        $value = fetch($url, $drive);
        set_transient('dgdg_nonce_' . $nonce, $value, 24 * HOUR_IN_SECONDS);
    }

    $images = [];
    $c = 0;
    if ($drive === false) {
        // Google Photos data structure for images
        $value = $value[1];
        foreach ($value as $image) {
            $images[] = '<img src="' . $image[1][0] . '" alt="">';
            $c++;
            if ($c == $limit) {
                break;
            }
        }
    } else {
        foreach ($value[0] as $image) {
            if (is_array($selected_images) && !in_array($image[2], $selected_images)) {
                continue;
            }
            $images[] = '<img src="https://drive.google.com/thumbnail?id=' . $image[0] . '&sz=' . $dimension . '" alt="">';
            $c++;
            if ($c == $limit) {
                break;
            }
        }
    }

    $value = '';
    foreach ($images as $image) {
        $value .= '<div class="item"><a href="' . $url . '" target="_blank">' . $image . '</a></div>';
    }

    return '<div class="dgdg-gallery-container" data-dgdg-nonce="' . $nonce . '">' . $value . '</div>';
}

function fetch($url, $drive)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt(
        $ch,
        CURLOPT_USERAGENT,
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15'
    );
    $result = curl_exec($ch);
    curl_close($ch);

    if ($drive === false) {
        preg_match(
            '/AF_initDataCallback\(\{key: \'ds:0\', isError:  false , hash: \'1\', data:function\(\)\{return (\[.*?\])\s+\}\}\);/is',
            $result,
            $m
        );
        $data = str_replace('\\n', PHP_EOL, $m[1]);
    } else {
        preg_match('/window\[\'_DRIVE_ivd\'\] = \'(.*?)\'/is', $result, $m);
        $data = str_replace('\\n', PHP_EOL, $m[1]);

        $data = preg_replace_callback(
            '/\\\x([a-f0-9][a-f0-9])/is',
            function ($v) {
                return chr(hexdec($v[1]));
            },
            $data
        );
    }

    return json_decode($data);
}