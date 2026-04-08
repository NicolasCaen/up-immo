<?php
namespace UpImmo\Admin;

class SettingsPage {
    public function __construct() {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addSettingsPage() {
        add_submenu_page(
            'edit.php?post_type=bien',
            'UpImmo Settings',
            'Settings',
            'manage_options',
            'up-immo-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings() {
        register_setting('up_immo_settings', 'up_immo_delete_images_with_bien');
        register_setting('up_immo_settings', 'up_immo_remove_missing_images');
        register_setting('up_immo_settings', 'up_immo_remove_manual_images');
        register_setting('up_immo_settings', 'up_immo_missing_bien_action');
    }

    public function renderSettingsPage() {
        $missing_bien_action = get_option('up_immo_missing_bien_action', 'none');
        ?>
        <div class="wrap">
            <h1>UpImmo Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('up_immo_settings'); ?>
                <?php do_settings_sections('up_immo_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="up_immo_delete_images_with_bien">
                                Supprimer les images avec le bien
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="up_immo_delete_images_with_bien" 
                                   name="up_immo_delete_images_with_bien" 
                                   value="1" 
                                   <?php checked(get_option('up_immo_delete_images_with_bien', 0)); ?> />
                            <p class="description">
                                Cochez cette case pour supprimer automatiquement toutes les images attachées 
                                lorsqu'un bien est supprimé. Attention, cette action est irréversible.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="up_immo_remove_missing_images">
                                Supprimer les images absentes lors de la mise à jour
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="up_immo_remove_missing_images" 
                                   name="up_immo_remove_missing_images" 
                                   value="1" 
                                   <?php checked(get_option('up_immo_remove_missing_images', 0)); ?> />
                            <p class="description">
                                Cochez cette case pour supprimer automatiquement les images d'import 
                                qui ne sont plus présentes dans le fichier d'import lors de la mise à jour d'un bien. 
                                Attention, cette action est irréversible.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="up_immo_remove_manual_images">
                                Supprimer les images manuelles lors de la mise à jour
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="up_immo_remove_manual_images" 
                                   name="up_immo_remove_manual_images" 
                                   value="1" 
                                   <?php checked(get_option('up_immo_remove_manual_images', 0)); ?> />
                            <p class="description">
                                Cochez cette case pour supprimer automatiquement les images attachées manuellement 
                                (sans URL source) lors de la mise à jour d'un bien. 
                                Attention, cette action est irréversible.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Biens absents du fichier d'import
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="up_immo_missing_bien_action" value="none" <?php checked($missing_bien_action, 'none'); ?> />
                                    Ne rien faire
                                </label><br />
                                <label>
                                    <input type="radio" name="up_immo_missing_bien_action" value="archive" <?php checked($missing_bien_action, 'archive'); ?> />
                                    Mettre en archive
                                </label><br />
                                <label>
                                    <input type="radio" name="up_immo_missing_bien_action" value="draft" <?php checked($missing_bien_action, 'draft'); ?> />
                                    Mettre en brouillon
                                </label><br />
                                <label>
                                    <input type="radio" name="up_immo_missing_bien_action" value="delete" <?php checked($missing_bien_action, 'delete'); ?> />
                                    Supprimer
                                </label>
                            </fieldset>
                            <p class="description">
                                Action appliquée aux biens existants qui ne sont plus présents dans le fichier d'import.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
