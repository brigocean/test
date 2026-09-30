<?php

if (!defined('ABSPATH')) {
    exit;
}

function likes_table_name()
{
    global $wpdb;
    return $wpdb->prefix . LIKES_TABLE;
}

function likes_install_table()
{
    global $wpdb;

    $table = likes_table_name();
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT UNSIGNED NOT NULL,
        vote TINYINT NOT NULL,
        ip VARCHAR(45) NOT NULL,
        page_url VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY post_ip (post_id, ip)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('after_switch_theme', 'likes_install_table');

function likes_client_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }

    return '0.0.0.0';
}

function likes_get_counts($post_id)
{
    global $wpdb;

    $table = likes_table_name();

    $up = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND vote = 1",
        $post_id
    ));

    $down = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND vote = -1",
        $post_id
    ));

    return ['up' => $up, 'down' => $down];
}

function likes_get_user_vote($post_id, $ip)
{
    global $wpdb;

    $table = likes_table_name();

    $vote = $wpdb->get_var($wpdb->prepare(
        "SELECT vote FROM {$table} WHERE post_id = %d AND ip = %s",
        $post_id,
        $ip
    ));

    return $vote === null ? 0 : (int) $vote;
}

function likes_handle_vote()
{
    check_ajax_referer('article_like', 'nonce');

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $vote = isset($_POST['vote']) ? (int) $_POST['vote'] : 0;

    if (!$post_id || get_post_status($post_id) !== 'publish') {
        wp_send_json_error('post not found');
    }

    if ($vote !== 1 && $vote !== -1) {
        wp_send_json_error('bad vote');
    }

    global $wpdb;
    $table = likes_table_name();
    $ip = likes_client_ip();
    $page = isset($_POST['page_url']) ? esc_url_raw(wp_unslash($_POST['page_url'])) : '';
    $current = likes_get_user_vote($post_id, $ip);

    if ($current === $vote) {
        $wpdb->delete($table, ['post_id' => $post_id, 'ip' => $ip]);
        $vote = 0;
    } else {
        $wpdb->replace($table, [
            'post_id'    => $post_id,
            'vote'       => $vote,
            'ip'         => $ip,
            'page_url'   => $page,
            'created_at' => current_time('mysql'),
        ]);
    }

    $counts = likes_get_counts($post_id);
    $counts['vote'] = $vote;

    wp_send_json_success($counts);
}
add_action('wp_ajax_article_like', 'likes_handle_vote');
add_action('wp_ajax_nopriv_article_like', 'likes_handle_vote');
