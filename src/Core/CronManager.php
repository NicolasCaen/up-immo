<?php

namespace UpImmo\Core;

use UpImmo\Import\ImportManager;
use UpImmo\Import\Strategies\CSVImportStrategy;
use WP_REST_Request;
use WP_REST_Response;

class CronManager {
    public function __construct() {
        add_action('up_immo_import_cron', [$this, 'handleCronImport']);
        add_action('rest_api_init', [$this, 'registerImportEndpoint']);
    }

    public function scheduleCron(): void {
        if (!wp_next_scheduled('up_immo_import_cron')) {
            wp_schedule_event(time(), 'hourly', 'up_immo_import_cron');
        }
    }

    public function clearCron(): void {
        $timestamp = wp_next_scheduled('up_immo_import_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'up_immo_import_cron');
        }
    }

    public function handleCronImport(): void {
        if (!defined('DOING_CRON') || !DOING_CRON) {
            return;
        }

        $importManager = new ImportManager();
        $importManager->setStrategy(new CSVImportStrategy());
        $importManager->handleImport();
    }

    public function registerImportEndpoint(): void {
        register_rest_route('up-immo/v1', '/import', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'handleImportRequest'],
            'permission_callback' => function() {
                return true;
            }
        ]);
    }

    public function handleImportRequest(WP_REST_Request $request): WP_REST_Response {
        $importManager = new ImportManager();
        $importManager->setStrategy(new CSVImportStrategy());
        $importManager->handleImport();

        return new WP_REST_Response(['message' => 'Importation lancée avec succès'], 200);
    }
} 