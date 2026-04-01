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
    }

    public function renderSettingsPage() {
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
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
