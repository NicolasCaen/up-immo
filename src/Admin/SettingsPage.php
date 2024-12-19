<?php

namespace UpImmo\Admin;

class SettingsPage {
    public function __construct() {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addSettingsPage() {
        add_submenu_page(
            'edit.php?post_type=bien', // Parent slug
            __('Paramètres', 'up-immo'), // Page title
            __('Paramètres', 'up-immo'), // Menu title
            'manage_options', // Capability
            'up-immo-settings', // Menu slug
            [$this, 'renderSettingsPage'] // Callback
        );
    }

    public function renderSettingsPage() {
        require_once UP_IMMO_PATH . 'templates/admin/settings-page.php';
    }

    public function registerSettings() {
        register_setting('up_immo_settings', 'up_immo_import_path', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);

        register_setting('up_immo_settings', 'up_immo_mapping_json', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'validateJson'],
            'default' => ''
        ]);
    }

    public function validateJson($input) {
        if (empty($input)) {
            return '';
        }

        $json = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            add_settings_error(
                'up_immo_mapping_json',
                'invalid_json',
                __('Le format JSON n\'est pas valide', 'up-immo')
            );
            return get_option('up_immo_mapping_json', '');
        }

        return $input;
    }
} 