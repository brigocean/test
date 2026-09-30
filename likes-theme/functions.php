<?php

if (!defined('ABSPATH')) {
    exit;
}

define('LIKES_TABLE', 'article_likes');

require_once get_template_directory() . '/inc/likes.php';
require_once get_template_directory() . '/inc/admin-stats.php';

function likes_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');

    register_nav_menus([
        'primary' => 'Главное меню',
    ]);
}
add_action('after_setup_theme', 'likes_setup');

function likes_assets()
{
    wp_enqueue_style('likes-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap', [], null);
    wp_enqueue_style('likes-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0');

    wp_enqueue_script('likes-front', get_template_directory_uri() . '/assets/js/likes.js', [], '1.0', true);
    wp_localize_script('likes-front', 'LikesData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('article_like'),
    ]);
}
add_action('wp_enqueue_scripts', 'likes_assets');

function likes_excerpt($length = 30)
{
    $text = get_the_excerpt();
    $words = preg_split('/\s+/u', wp_strip_all_tags($text));

    if (count($words) > $length) {
        $words = array_slice($words, 0, $length);
        return implode(' ', $words) . '…';
    }

    return implode(' ', $words);
}
