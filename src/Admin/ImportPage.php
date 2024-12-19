<?php

namespace UpImmo\Admin;

class ImportPage {
    public function __construct() {
        add_action('admin_menu', [$this, 'addImportPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function addImportPage(): void {
        add_submenu_page(
            'edit.php?post_type=bien',
            __('Import', 'up-immo'),
            __('Import', 'up-immo'),
            'manage_options',
            'up-immo-import',
            [$this, 'renderImportPage']
        );
    }

    public function renderImportPage(): void {
        require_once UP_IMMO_PATH . 'templates/admin/import-page.php';
    }

    public function enqueueScripts($hook): void {
        
        if ('bien_page_up-immo-import' !== $hook) {
           // error_log('Current hook :) : ' . $hook);
            return;
        }

        wp_enqueue_script(
            'up-immo-admin',
            UP_IMMO_URL . 'assets/js/admin.js',
            ['jquery'],
            UP_IMMO_VERSION,
            true
        );

        wp_localize_script('up-immo-admin', 'up_immo_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'messages' => [
                'no_strategy' => __('Veuillez sélectionner une stratégie d\'import', 'up-immo'),
                'starting' => __('Démarrage de l\'import...', 'up-immo'),
                'success' => __('Import terminé avec succès', 'up-immo'),
                'error' => __('Une erreur est survenue', 'up-immo')
            ]
        ]);
    }
} 