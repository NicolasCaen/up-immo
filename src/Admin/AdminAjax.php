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

            $file_path = sanitize_text_field($_POST['file_path'] ?? '');
            $strategy = sanitize_text_field($_POST['import_strategy'] ?? '');
            
            if (!empty($file_path)) {
                update_option('up_immo_import_path', $file_path);
            } else {
                $file_path = get_option('up_immo_import_path', '');
            }

            if (empty($file_path)) {
                throw new \Exception('Aucun chemin de fichier spécifié');
            }

            if (empty($strategy)) {
                throw new \Exception('Aucune stratégie d\'import sélectionnée');
            }

            $importContext = new \UpImmo\Import\ImportContext();
            
            // Ajouter l'encodage UTF-8 pour le traitement du CSV
            setlocale(LC_ALL, 'fr_FR.UTF-8');
            
            // Sélectionner la stratégie appropriée
            switch ($strategy) {
                case 'csv':
                    $importStrategy = new \UpImmo\Import\Strategies\CSVImportStrategy();
                    // Configurer l'encodage pour la stratégie CSV
                    $importStrategy->setEncoding('UTF-8');
                    $importContext->setStrategy($importStrategy);
                    break;
                default:
                    throw new \Exception('Stratégie d\'import non valide');
            }

            $results = $importContext->import($file_path);
            
            wp_send_json_success($results);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
} 