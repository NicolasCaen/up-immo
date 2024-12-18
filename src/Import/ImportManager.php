<?php
namespace UpImmo\Import;

use UpImmo\Core\Singleton;
use UpImmo\Import\Strategies\CSVImportStrategy;

class ImportManager extends Singleton {
    private const OPTION_NAME = 'up_immo_import_path';
    private $importContext;

    public function __construct() {
        $this->importContext = new ImportContext();
        add_action('wp_ajax_up_immo_import', [$this, 'handleImport']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_menu', [$this, 'addImportPage']);
    }

    public function registerSettings(): void {
        register_setting('up_immo_options', self::OPTION_NAME);
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
        include UP_IMMO_PATH . 'templates/admin/import-page.php';
    }

    public function handleImport(): void {
        try {
            check_ajax_referer('up_immo_import', 'nonce');

            $file_path = sanitize_text_field($_POST['file_path'] ?? '');
            
            if (!empty($file_path)) {
                // Sauvegarder le chemin dans les options
                update_option(self::OPTION_NAME, $file_path);
            } else {
                // Utiliser le chemin sauvegardé
                $file_path = get_option(self::OPTION_NAME, '');
            }

            if (empty($file_path)) {
                throw new \Exception('Aucun chemin de fichier spécifié');
            }

            $results = $this->importContext->import($file_path);
            wp_send_json_success($results);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function getLastImportPath(): string {
        return get_option(self::OPTION_NAME, '');
    }
} 