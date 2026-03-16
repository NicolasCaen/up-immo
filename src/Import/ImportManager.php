<?php
namespace UpImmo\Import;

use UpImmo\Core\Singleton;
use UpImmo\Import\Strategies\CSVImportStrategy;

class ImportManager extends Singleton {
    private const OPTION_IMPORT_PATH = 'up_immo_import_path';
    private const OPTION_BROWSER_CRON_ENABLED = 'up_immo_browser_cron_enabled';
    private const OPTION_BROWSER_CRON_TOKEN = 'up_immo_browser_cron_token';

    private $context;

    protected function __construct() {
        parent::__construct();
        $this->context = new ImportContext();
        add_action('admin_menu', [$this, 'addImportPage']);
        add_action('wp_ajax_up_immo_import', [$this, 'handleImport']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_post_up_immo_save_import_settings', [$this, 'handleSettingsSave']);
        add_action('init', [$this, 'maybeHandleBrowserCron']);
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
            $file_path = sanitize_text_field($_POST['file_path'] ?? '');
            if (!$file_path) {
                $file_path = $this->getDefaultFilePath();
            }

            if (!$file_path) {
                throw new \Exception(__('Aucun chemin de fichier n\'a été fourni.', 'up-immo'));
            }

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

    public function getDefaultFilePath(): string {
        return get_option(self::OPTION_IMPORT_PATH, '');
    }

    public function isBrowserCronEnabled(): bool {
        return (bool) get_option(self::OPTION_BROWSER_CRON_ENABLED, false);
    }

    public function getBrowserCronToken(): string {
        $token = get_option(self::OPTION_BROWSER_CRON_TOKEN, '');

        if (empty($token)) {
            $token = wp_generate_password(32, false, false);
            update_option(self::OPTION_BROWSER_CRON_TOKEN, $token);
        }

        return $token;
    }

    public function getBrowserCronUrl(): string {
        return add_query_arg(
            [
                'up_immo_cron' => 1,
                'token' => $this->getBrowserCronToken()
            ],
            home_url('/')
        );
    }

    public function handleSettingsSave(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'up-immo'));
        }

        check_admin_referer('up_immo_import_settings');

        $file_path = sanitize_text_field($_POST['default_file_path'] ?? '');
        $browser_enabled = isset($_POST['browser_cron_enabled']) ? 1 : 0;

        update_option(self::OPTION_IMPORT_PATH, $file_path);
        update_option(self::OPTION_BROWSER_CRON_ENABLED, $browser_enabled);

        if (!empty($_POST['regenerate_token'])) {
            delete_option(self::OPTION_BROWSER_CRON_TOKEN);
            $this->getBrowserCronToken();
        } else {
            $this->getBrowserCronToken(); // ensure token exists
        }

        wp_safe_redirect($this->getSettingsRedirectUrl());
        exit;
    }

    private function getSettingsRedirectUrl(): string {
        return add_query_arg(
            [
                'post_type' => 'bien',
                'page' => 'up-immo-import',
                'settings-updated' => 'true'
            ],
            admin_url('edit.php')
        );
    }

    public function maybeHandleBrowserCron(): void {
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }

        if (!isset($_GET['up_immo_cron'])) {
            return;
        }

        if (!defined('UP_IMMO_BROWSER_CRON_REQUEST')) {
            define('UP_IMMO_BROWSER_CRON_REQUEST', true);
        } else {
            return;
        }

        if (!$this->isBrowserCronEnabled()) {
            wp_send_json_error([
                'message' => __('Le déclenchement via navigateur est désactivé.', 'up-immo')
            ], 403);
        }

        $token = sanitize_text_field($_GET['token'] ?? '');

        if (empty($token) || $token !== $this->getBrowserCronToken()) {
            wp_send_json_error([
                'message' => __('Token invalide.', 'up-immo')
            ], 403);
        }

        $file_path = $this->getDefaultFilePath();

        if (empty($file_path)) {
            wp_send_json_error([
                'message' => __('Aucun chemin d\'import n\'est configuré.', 'up-immo')
            ], 400);
        }

        $this->context->setStrategy(new CSVImportStrategy());
        $results = $this->context->import($file_path);

        wp_send_json_success($results);
    }
}