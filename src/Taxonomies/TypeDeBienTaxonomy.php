<?php
namespace UpImmo\Taxonomies;

class TypeDeBienTaxonomy {
    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_taxonomy('type_de_bien', ['bien'], [
            'labels' => [
                'name' => __('Types de bien', 'up-immo'),
                'singular_name' => __('Type de bien', 'up-immo'),
            ],
            'hierarchical' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'type-de-bien'],
        ]);
    }
} 