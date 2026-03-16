<?php
if (!defined('ABSPATH')) exit;

$import_manager = \UpImmo\Import\ImportManager::getInstance();
$default_path = $import_manager->getDefaultFilePath();
$browser_cron_enabled = $import_manager->isBrowserCronEnabled();
$browser_cron_url = $import_manager->getBrowserCronUrl();
?>
<div class="wrap">
    <h1><?php _e('Import de biens', 'up-immo'); ?></h1>

    <?php if (isset($_GET['settings-updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Paramètres enregistrés.', 'up-immo'); ?></p>
        </div>
    <?php endif; ?>

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

        <hr>

        <h2><?php _e('Paramètres d\'import automatique', 'up-immo'); ?></h2>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="up-immo-import-settings">
            <?php wp_nonce_field('up_immo_import_settings'); ?>
            <input type="hidden" name="action" value="up_immo_save_import_settings">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_file_path"><?php _e('Chemin de fichier par défaut', 'up-immo'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="default_file_path"
                               name="default_file_path"
                               class="regular-text"
                               value="<?php echo esc_attr($default_path); ?>">
                        <p class="description">
                            <?php _e('Utilisé lorsque le champ ci-dessus est laissé vide. Exemple : /uploads/imports', 'up-immo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Déclenchement via navigateur', 'up-immo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="browser_cron_enabled" <?php checked($browser_cron_enabled); ?>>
                            <?php _e('Autoriser l\'exécution de l\'import via une URL sécurisée', 'up-immo'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Permet d\'exécuter le cron en visitant simplement une URL (utile pour les hébergements sans cron serveur).', 'up-immo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('URL de déclenchement', 'up-immo'); ?></th>
                    <td>
                        <code><?php echo esc_html($browser_cron_url); ?></code>
                        <p class="description">
                            <?php _e('Ajoutez cette URL à un cron ou visitez-la dans le navigateur pour lancer l\'import. Le token est requis pour la sécurité.', 'up-immo'); ?>
                        </p>
                        <label>
                            <input type="checkbox" name="regenerate_token" value="1">
                            <?php _e('Régénérer le token (utilisez si l\'URL est compromise).', 'up-immo'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <button type="submit" class="button button-secondary">
                <?php _e('Enregistrer les paramètres', 'up-immo'); ?>
            </button>
        </form>
    </div>
</div>