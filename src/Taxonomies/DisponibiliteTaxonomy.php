<?php
namespace UpImmo\Taxonomies;

class DisponibiliteTaxonomy {
    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_taxonomy('disponibilite', ['bien'], [
            'labels' => [
                'name' => __('Disponibilités', 'up-immo'),
                'singular_name' => __('Disponibilité', 'up-immo'),
            ],
            'hierarchical' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
            "multiple" => false,
            'rewrite' => ['slug' => 'disponibilite'],
        ]);
    }
} 