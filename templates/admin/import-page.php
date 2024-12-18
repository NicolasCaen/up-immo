<?php
if (!defined('ABSPATH')) exit;
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

        <div class="up-immo-progress" style="display:none;">
            <div id="up-immo-progress-message"></div>
            <div class="progress-bar">
                <div id="up-immo-progress-bar" style="width: 0%"></div>
            </div>
        </div>

        <form id="upImmoImportForm" class="up-immo-import-form">
            <?php wp_nonce_field('up_immo_import', 'up_immo_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="file_path"><?php _e('Chemin du fichier', 'up-immo'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="file_path" 
                               name="file_path" 
                               class="regular-text" 
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