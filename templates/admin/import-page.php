<?php
if (!defined('ABSPATH')) exit;

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
            </table>

            <button type="submit" class="button button-primary">
                <?php _e('Lancer l\'import', 'up-immo'); ?>
            </button>
        </form>
    </div>
</div> 



<div class="wrap">

        
        <div id="up-immo-import-logs" style="
            background: #fff;
            padding: 15px;
            border: 1px solid #ccd0d4;
            margin: 20px 0;
            height: 400px;
            overflow-y: auto;
            font-family: monospace;
        ">
            <div id="up-immo-logs-content"></div>
        </div>


    </div>

    <script>
    jQuery(document).ready(function($) {
        function refreshLogs() {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'up_immo_get_logs'
                },
                success: function(response) {
                    if (response.success) {
                        const logsHtml = response.data.map(log => 
                            `<div class="log-entry">
                                <span class="log-time">[${log.time}]</span> 
                                ${log.message}
                            </div>`
                        ).join('');
                        
                        $('#up-immo-logs-content').html(logsHtml);
                        
                        // Auto-scroll vers le bas
                        const logsDiv = document.getElementById('up-immo-import-logs');
                        logsDiv.scrollTop = logsDiv.scrollHeight;
                    }
                }
            });
        }

        // Rafraîchir les logs toutes les 2 secondes pendant l'import
        setInterval(refreshLogs, 2000);
    });
    </script>

    <style>
    .log-entry {
        padding: 3px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .log-time {
        color: #666;
        margin-right: 10px;
    }
    </style>