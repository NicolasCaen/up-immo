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
    }

    public function handleImport(): void {
        try {
            check_ajax_referer('up_immo_admin', 'nonce');

            $strategy = sanitize_text_field($_POST['import_strategy'] ?? '');
            if (empty($strategy)) {
                throw new \Exception(__('Aucune stratégie d\'import sélectionnée', 'up-immo'));
            }

            // Récupérer le chemin depuis les options
            $file_path = get_option(self::OPTION_NAME, '');
            if (empty($file_path)) {
                throw new \Exception(__('Aucun chemin de fichier configuré dans les paramètres', 'up-immo'));
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