<?php

if (!defined('ABSPATH')) {
    exit;
}

function likes_admin_menu()
{
    add_menu_page(
        'Статистика лайков',
        'Лайки статей',
        'manage_options',
        'article-likes',
        'likes_stats_page',
        'dashicons-thumbs-up',
        26
    );
}
add_action('admin_menu', 'likes_admin_menu');

function likes_stats_page()
{
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    $table = new Likes_Stats_Table();
    $table->prepare_items();

    echo '<div class="wrap">';
    echo '<h1>Статистика лайков</h1>';
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="article-likes">';
    $table->display();
    echo '</form>';
    echo '</div>';
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Likes_Stats_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'article',
            'plural'   => 'articles',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'title' => 'Статья',
            'up'    => 'За',
            'down'  => 'Против',
            'total' => 'Всего голосов',
        ];
    }

    protected function get_sortable_columns()
    {
        return [
            'up'    => ['up', false],
            'down'  => ['down', false],
            'total' => ['total', true],
        ];
    }

    public function prepare_items()
    {
        global $wpdb;

        $table = likes_table_name();
        $posts = $wpdb->posts;

        $rows = $wpdb->get_results(
            "SELECT l.post_id,
                    p.post_title,
                    SUM(l.vote = 1) AS up,
                    SUM(l.vote = -1) AS down,
                    COUNT(*) AS total
             FROM {$table} l
             INNER JOIN {$posts} p ON p.ID = l.post_id
             GROUP BY l.post_id, p.post_title",
            ARRAY_A
        );

        if (!$rows) {
            $rows = [];
        }

        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'total';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'asc' : 'desc';

        usort($rows, function ($a, $b) use ($orderby, $order) {
            $one = isset($a[$orderby]) ? (int) $a[$orderby] : 0;
            $two = isset($b[$orderby]) ? (int) $b[$orderby] : 0;
            $result = $one <=> $two;
            return $order === 'asc' ? $result : -$result;
        });

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $this->items = $rows;
    }

    public function column_title($item)
    {
        $link = get_edit_post_link($item['post_id']);
        $title = $item['post_title'] !== '' ? $item['post_title'] : '(без названия)';

        if ($link) {
            return '<a href="' . esc_url($link) . '"><strong>' . esc_html($title) . '</strong></a>';
        }

        return '<strong>' . esc_html($title) . '</strong>';
    }

    public function column_default($item, $column)
    {
        return isset($item[$column]) ? (int) $item[$column] : 0;
    }

    public function no_items()
    {
        echo 'Голосов пока нет.';
    }
}
