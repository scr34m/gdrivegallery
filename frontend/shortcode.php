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
    $columns = 3;
    $dimension = 'h187';
    extract($atts);

    return html($path, $limit, $columns, $dimension);
}

function html($url, $limit, $columns, $dimension)
{
    wp_enqueue_style('dgdg_gallery_css');

    $nonce = hash('sha256', $url . $limit . $columns);

    if (false === ($value = get_transient('dgdg_nonce_' . $nonce))) {
        $value = fetch($url);
        set_transient('dgdg_nonce_' . $nonce, $value, 24 * HOUR_IN_SECONDS);
    }

    $images = [];
    $c = 0;
    foreach ($value[0] as $image) {
        $images[] = $image[0];
        $c++;
        if ($c == $limit) {
            break;
        }
    }

    $value = '';
    foreach (array_chunk($images, ceil($limit / $columns)) as $col) {
        $value .= '<div class="col">';
        foreach ($col as $image) {
            $value .= '<a href="' . $url . '" target="_blank"><img src="https://drive.google.com/thumbnail?id=' . $image . '&sz=' . $dimension . '" alt=""></a>';
        }
        $value .= '</div>';
    }

    return '<div class="dgdg-gallery-container" data-dgdg-nonce="' . $nonce . '">' . $value . '</div>';
}

function fetch($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
        $ch,
        CURLOPT_USERAGENT,
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15'
    );
    $result = curl_exec($ch);
    curl_close($ch);

    preg_match('/window\[\'_DRIVE_ivd\'\] = \'(.*?)\'/is', $result, $m);
    $data = str_replace('\\n', PHP_EOL, $m[1]);

    $data = preg_replace_callback(
        '/\\\x([a-f0-9][a-f0-9])/is',
        function ($v) {
            return chr(hexdec($v[1]));
        },
        $data
    );

    return json_decode($data);
}