<?php

namespace UpImmo\Core;

use UpImmo\Filters\ContentFilters;

class GutenbergManager {
    private $contentFilters;

    public function __construct() {
        $this->contentFilters = new ContentFilters();
        add_action('init', [$this, 'registerBlocks']);
        add_action('rest_api_init', [$this, 'registerMetaFields']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
    }

    public function registerBlocks(): void {
        register_block_type('up-immo/bien-meta', [
            'editor_script' => 'up-immo-editor',
            'editor_style' => 'up-immo-editor',
            'style' => 'up-immo-style',
            'render_callback' => [$this, 'renderBienMeta'],
            'attributes' => [
                'field' => [
                    'type' => 'string',
                    'default' => 'prix'
                ],
                'className' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ]
        ]);
    }

    public function registerMetaFields(): void {
        $fields = ['reference', 'prix', 'surface', 'pieces', 'chambres', 'code_postal', 'ville', 'description', 'dpe'];
        
        foreach ($fields as $field) {
            register_post_meta('bien', $field, [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                }
            ]);
        }
    }

    public function renderBienMeta($attributes, $content): string {
        if (empty($attributes['field'])) {
            return '';
        }

        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }

        // Récupérer la valeur
        $value = '';
        switch ($attributes['field']) {
            case 'titre':
                $value = get_the_title($post_id);
                break;
            default:
                $value = get_post_meta($post_id, $attributes['field'], true);
                break;
        }

        if (empty($value)) {
            return '';
        }

        // Appliquer les filtres
        $filtered_value = ContentFilters::applyFilters($value, $attributes['field']);

        // Récupérer les attributs du bloc avec les classes par défaut
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'wp-block-up-immo-bien-meta'
        ]);

        // Retourner le HTML avec les styles préservés
        return sprintf(
            '<div %1$s>%2$s</div>',
            $wrapper_attributes,
            wp_kses_post($filtered_value)
        );
    }

    private function getFieldLabel(string $field): string {
        $labels = [
            'titre' => 'Titre',
            'description' => 'Description',
            'prix' => 'Prix',
            'surface' => 'Surface',
            'pieces' => 'Pièces',
            'chambres' => 'Chambres',
            'code_postal' => 'Code Postal',
            'ville' => 'Ville',
            'reference' => 'Référence',
            'dpe' => 'DPE'
        ];

        return $labels[$field] ?? ucfirst($field);
    }

    public function enqueueEditorAssets(): void {
        $asset_file = include(UP_IMMO_PATH . 'assets/js/build/editor.asset.php');

        wp_register_script(
            'up-immo-editor',
            plugins_url('assets/js/build/editor.js', UP_IMMO_PLUGIN_FILE),
            array_merge(['wp-blocks', 'wp-element', 'wp-components', 'wp-editor'], $asset_file['dependencies'] ?? []),
            $asset_file['version'] ?? UP_IMMO_VERSION
        );

        wp_register_style(
            'up-immo-editor',
            plugins_url('assets/css/editor.css', UP_IMMO_PLUGIN_FILE),
            [],
            UP_IMMO_VERSION
        );

        wp_register_style(
            'up-immo-style',
            plugins_url('assets/css/style.css', UP_IMMO_PLUGIN_FILE),
            [],
            UP_IMMO_VERSION
        );

        wp_enqueue_script('up-immo-editor');
        wp_enqueue_style('up-immo-editor');
    }
} 