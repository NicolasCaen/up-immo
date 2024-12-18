<?php
namespace UpImmo\Taxonomies;

class VilleTaxonomy {
    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_taxonomy('ville', ['bien'], [
            'labels' => [
                'name' => __('Villes', 'up-immo'),
                'singular_name' => __('Ville', 'up-immo'),
            ],
            'hierarchical' => false,
            "multiple" => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'ville'],
        ]);
    }
} 