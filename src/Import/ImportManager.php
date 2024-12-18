<?php
namespace UpImmo\Import;

use UpImmo\Core\Singleton;
use UpImmo\Import\Strategies\CSVImportStrategy;

class ImportManager extends Singleton {
    private $context;

    protected function __construct() {
        parent::__construct();
        $this->context = new ImportContext();
        add_action('admin_menu', [$this, 'addImportPage']);
        add_action('wp_ajax_up_immo_import', [$this, 'handleImport']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
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
        check_ajax_referer('up_immo_import', 'security');

        try {
            $file_path = $_POST['file_path'] ?? '';
            
            $this->context->setStrategy(new CSVImportStrategy());
            $results = $this->context->import($file_path);

            wp_send_json_success([
                'message' => sprintf(__('%d biens importés avec succès', 'up-immo'), count($results)),
                'progress' => $this->context->getProgress()
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function enqueueAssets($hook): void {
        if ($hook !== 'bien_page_up-immo-import') {
            return;
        }

        wp_enqueue_style(
            'up-immo-admin',
            UP_IMMO_URL . 'assets/css/admin.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'up-immo-admin',
            UP_IMMO_URL . 'assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }
} 