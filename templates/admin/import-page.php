<?php
if (!defined('ABSPATH')) exit;
$last_path = get_option('up_immo_import_path', '');

// Liste des stratégies disponibles
$strategies = [
    'csv' => __('Import CSV - Hektor', 'up-immo'),
];
?>
<div class="wrap">
    <h1><?php _e('Import de biens', 'up-immo'); ?></h1>

    <div class="up-immo-import-container">
        <div class="up-immo-import-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-bar__fill"></div>
            </div>
            <div class="progress-text"></div>
        </div>

        <form id="upImmoImportForm" class="up-immo-import-form" method="post">
            <?php wp_nonce_field('up_immo_admin', 'nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="import_strategy"><?php _e('Type d\'import', 'up-immo'); ?></label>
                    </th>
                    <td>
                        <select id="import_strategy" name="import_strategy" required>
                            <option value=""><?php _e('Sélectionner une stratégie', 'up-immo'); ?></option>
                            <?php foreach ($strategies as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>">
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Choisissez le type de fichier à importer', 'up-immo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="file_path"><?php _e('Chemin du fichier', 'up-immo'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="file_path" 
                               name="file_path" 
                               class="regular-text" 
                               value="<?php echo esc_attr($last_path); ?>"
                               required>
                        <p class="description">
                            <?php _e('Chemin relatif depuis wp-content/', 'up-immo'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <button type="submit" class="button button-primary">
                <?php _e('Lancer l\'import', 'up-immo'); ?>
            </button>
        </form>
    </div>
</div> 