<?php
if (!defined('ABSPATH')) exit;

// Récupération des options
$default_mapping = get_option('up_immo_mapping_json', '');
$import_path = get_option('up_immo_import_path', '');
?>

<div class="wrap">
    <h1><?php _e('Paramètres Up Immo', 'up-immo'); ?></h1>

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
</div> 