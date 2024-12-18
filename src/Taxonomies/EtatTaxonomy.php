<?php
namespace UpImmo\Taxonomies;

class EtatTaxonomy {
    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_taxonomy('etat', ['bien'], [
            'labels' => [
                'name' => __('États', 'up-immo'),
                'singular_name' => __('État', 'up-immo'),
            ],
            'hierarchical' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'etat'],
        ]);
    }
} 