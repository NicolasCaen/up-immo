<?php
namespace UpImmo\PostTypes;

class BienPostType {
    public function __construct() {
        add_action('init', [$this, 'register']);
        add_filter('manage_bien_posts_columns', [$this, 'addThumbnailColumn']);
        add_action('manage_bien_posts_custom_column', [$this, 'displayThumbnailColumn'], 10, 2);
    }

    public function register(): void {
        register_post_type('bien', [
            'labels' => [
                'name' => __('Biens', 'up-immo'),
                'singular_name' => __('Bien', 'up-immo'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_icon' => 'dashicons-building',
            'rewrite' => ['slug' => 'biens'],
        ]);
    }

    public function addThumbnailColumn($columns): array {
        $columns['thumbnail'] = __('Miniature', 'up-immo');
        return $columns;
    }

    public function displayThumbnailColumn($column, $post_id): void {
        if ($column === 'thumbnail') {
            echo get_the_post_thumbnail($post_id, [50, 50]);
        }
    }
} 