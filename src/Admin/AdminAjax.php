<?php

namespace UpImmo\Admin;

class AdminAjax {
    public function __construct() {
        add_action('wp_ajax_up_immo_get_progress', [$this, 'getProgress']);
        add_action('wp_ajax_up_immo_import', [$this, 'handleImport']);
    }

    public function getProgress() {
        check_ajax_referer('up_immo_admin', 'nonce');

        $progress = get_option('up_immo_import_progress', [
            'message' => 'En attente...',
            'percentage' => 0,
            'timestamp' => time()
        ]);
        
        if (!is_array($progress)) {
            $progress = [
                'message' => 'Erreur de format',
                'percentage' => 0,
                'timestamp' => time()
            ];
        }

        wp_send_json_success($progress);
    }

    public function handleImport() {
        try {
            check_ajax_referer('up_immo_admin', 'nonce');

            // Récupérer le chemin du fichier depuis les options
            $file_path = get_option('up_immo_import_path', '');
            $strategy = sanitize_text_field($_POST['import_strategy'] ?? '');

            if (empty($file_path)) {
                throw new \Exception(__('Aucun chemin de fichier configuré dans les paramètres', 'up-immo'));
            }

            if (empty($strategy)) {
                throw new \Exception(__('Aucune stratégie d\'import sélectionnée', 'up-immo'));
            }

            // Log pour le debug
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Début import avec stratégie: ' . $strategy);
                error_log('UP_IMMO - Chemin du fichier: ' . $file_path);
            }

            // Initialiser le contexte d'import
            $importContext = new \UpImmo\Import\ImportContext();
            
            // Sélectionner la stratégie
            switch ($strategy) {
                case 'csv':
                    $importStrategy = new \UpImmo\Import\Strategies\CSVImportStrategy();
                    $importStrategy->setEncoding('UTF-8');
                    $importContext->setStrategy($importStrategy);
                    break;
                default:
                    throw new \Exception(__('Stratégie d\'import non valide', 'up-immo'));
            }

            // Mettre à jour le statut initial
            update_option('up_immo_import_progress', [
                'message' => __('Démarrage de l\'import...', 'up-immo'),
                'percentage' => 0,
                'timestamp' => time()
            ]);

            // Lancer l'import
            $results = $importContext->import($file_path);
            
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Résultats import: ' . print_r($results, true));
            }

            wp_send_json_success([
                'message' => __('Import terminé avec succès', 'up-immo'),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            error_log('UP_IMMO - Erreur import: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
} 