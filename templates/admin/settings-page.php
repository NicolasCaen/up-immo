<?php
if (!defined('ABSPATH')) exit;

// Récupération des options
$default_mapping = get_option('up_immo_mapping_json', '');
$import_path = get_option('up_immo_import_path', '');

// Récupération des données du fichier sample.csv
// $sampleCsvData est passé depuis la méthode renderSettingsPage
?>

<div class="wrap">
    <h1><?php _e('Paramètres Up Immo', 'up-immo'); ?></h1>
    
    <style>
        .csv-table-container {
            margin: 20px 0;
            max-width: 100%;
            overflow-x: auto;
        }
        .csv-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }
        .csv-table th, .csv-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
            white-space: nowrap;
        }
        .csv-table th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        .csv-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .csv-table tr:hover {
            background-color: #f0f0f0;
        }
        .csv-index {
            font-weight: bold;
            background-color: #e7e7e7;
            text-align: center;
        }
        .csv-table-title {
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .csv-help-text {
            margin-bottom: 15px;
        }
    </style>

    <form method="post" action="options.php">
        <?php 
        settings_fields('up_immo_settings');
        do_settings_sections('up_immo_settings');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="default_import_path"><?php _e('Chemin d\'import par défaut', 'up-immo'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="default_import_path" 
                           name="up_immo_import_path" 
                           value="<?php echo esc_attr($import_path); ?>" 
                           class="regular-text">
                    <p class="description">
                        <?php _e('Chemin relatif depuis wp-content/', 'up-immo'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="default_mapping"><?php _e('Mapping par défaut', 'up-immo'); ?></label>
                </th>
                <td>
                    <textarea id="default_mapping" 
                             name="up_immo_mapping_json" 
                             class="large-text code" 
                             rows="10"><?php echo esc_textarea($default_mapping); ?></textarea>
                    <p class="description">
                        <?php _e('Configuration du mapping des champs (JSON)', 'up-immo'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Enregistrer les paramètres', 'up-immo')); ?>
    </form>
    
    <h2 class="csv-table-title"><?php _e('Structure du fichier CSV d\'exemple', 'up-immo'); ?></h2>
    <p class="csv-help-text"><?php _e('Ce tableau présente la structure du fichier sample.csv. Utilisez les numéros d\'index (première ligne) pour configurer le mapping des colonnes dans le champ "Mapping par défaut" ci-dessus.', 'up-immo'); ?></p>
    
    <?php if (!empty($sampleCsvData) && isset($sampleCsvData['headers']) && isset($sampleCsvData['data'])): ?>
    <div class="csv-table-container">
        <table class="csv-table">
            <thead>
                <tr>
                    <th class="csv-index"><?php _e('Index', 'up-immo'); ?></th>
                    <?php foreach (range(0, count($sampleCsvData['headers']) - 1) as $index): ?>
                        <th class="csv-index"><?php echo esc_html($index); ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th><?php _e('En-tête', 'up-immo'); ?></th>
                    <?php foreach ($sampleCsvData['headers'] as $header): ?>
                        <th><?php echo esc_html(trim($header)); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleCsvData['data'] as $index => $row): ?>
                <tr>
                    <td><?php echo esc_html(sprintf(__('Ligne %d', 'up-immo'), $index + 1)); ?></td>
                    <?php foreach ($row as $value): ?>
                        <td><?php echo esc_html(trim($value)); ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="description">
        <?php _e('Note: Le tableau peut être défilé horizontalement pour voir toutes les colonnes.', 'up-immo'); ?>
    </p>
    
    <h3><?php _e('Exemple de mapping JSON', 'up-immo'); ?></h3>
    <pre style="background:#f5f5f5; padding:10px; overflow:auto;">
{
    "reference": 1,
    "titre": 19,
    "description": 20,
    "prix": 10,
    "surface": 15,
    "pieces": 17,
    "chambres": 18,
    "code_postal": 4,
    "ville": 5,
    "dpe": 173,
    "contact_tel": 97,
    "contact_email": 98
}
    </pre>
    <p class="description">
        <?php _e('Ce mapping associe les noms de champs utilisés par le plugin aux index des colonnes du fichier CSV.', 'up-immo'); ?>
    </p>
    <?php else: ?>
    <div class="notice notice-warning">
        <p><?php _e('Le fichier sample.csv n\'a pas pu être chargé ou est vide.', 'up-immo'); ?></p>
    </div>
    <?php endif; ?>
</div> 