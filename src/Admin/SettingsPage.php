<?php

namespace UpImmo\Admin;

class SettingsPage {
    private $sampleCsvData = [];
    
    public function __construct() {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Charger les données du fichier sample.csv
        $this->loadSampleCsvData();
    }
    
    /**
     * Charge et analyse le fichier sample.csv
     */
    private function loadSampleCsvData() {
        $sampleCsvPath = UP_IMMO_PATH . 'sample.csv';
        
        if (file_exists($sampleCsvPath)) {
            $content = file_get_contents($sampleCsvPath);
            if ($content !== false) {
                // Détecter l'encodage et convertir en UTF-8 si nécessaire
                $detected_encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'Windows-1252'], true);
                if ($detected_encoding && $detected_encoding !== 'UTF-8') {
                    $content = iconv($detected_encoding, 'UTF-8//TRANSLIT//IGNORE', $content);
                }
                
                // Diviser en lignes
                $lines = explode("\n", $content);
                
                if (count($lines) > 0) {
                    // Analyser l'en-tête (première ligne)
                    $headers = str_getcsv($lines[0], ';');
                    $this->sampleCsvData['headers'] = $headers;
                    
                    // Analyser toutes les lignes de données
                    $data = [];
                    for ($i = 1; $i < count($lines); $i++) {
                        if (!empty(trim($lines[$i]))) {
                            $row = str_getcsv($lines[$i], ';');
                            // Ne pas inclure les lignes vides (qui contiennent uniquement des point-virgules)
                            $isEmpty = true;
                            foreach ($row as $cell) {
                                if (!empty(trim($cell))) {
                                    $isEmpty = false;
                                    break;
                                }
                            }
                            if (!$isEmpty) {
                                $data[] = $row;
                            }
                        }
                    }
                    $this->sampleCsvData['data'] = $data;
                }
            }
        }
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
        // Passer les données CSV au template
        $sampleCsvData = $this->sampleCsvData;
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
    
    /**
     * Récupère les données du fichier sample.csv
     * @return array Tableau contenant les en-têtes et les données du fichier CSV
     */
    public function getSampleCsvData() {
        return $this->sampleCsvData;
    }
} 