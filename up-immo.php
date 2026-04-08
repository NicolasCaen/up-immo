<?php
/**
 * Plugin Name: UP Immo
 * Description: Plugin de gestion immobilière
 * Version: 1.4.3
 * Author: GEHIN Nicolas
 */

namespace UpImmo;

if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('UP_IMMO_VERSION', '1.4.3');
define('UP_IMMO_PLUGIN_FILE', __FILE__);
define('UP_IMMO_PATH', plugin_dir_path(__FILE__));
define('UP_IMMO_URL', plugin_dir_url(__FILE__));
define('DEBUG_UP_IMMO', false); // Constante de debug

// Définition des constantes manquantes
if (!defined('UP_IMMO_PLUGIN_FILE')) {
    define('UP_IMMO_PLUGIN_FILE', __FILE__);
}
if (!defined('DEBUG_UP_IMMO')) {
    define('DEBUG_UP_IMMO', WP_DEBUG);
}

// Custom Autoloader
function autoloader($class) {
    $prefix = 'UpImmo\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = UP_IMMO_PATH . 'src/' . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('UpImmo\autoloader');

// Pré-charger les classes essentielles
$base_dir = plugin_dir_path(__FILE__);
$files_to_load = [
    'src/Core/Singleton.php',
    'src/Core/Plugin.php',
    'src/Admin/AdminPage.php',
    'src/Admin/AdminAjax.php',
    'src/Admin/SettingsPage.php'
];

foreach ($files_to_load as $file_rel_path) {
    $full_path = $base_dir . $file_rel_path;
    if (file_exists($full_path)) {
        require_once $full_path;
    }
}

// Initialize plugin
function init_plugin() {
    \UpImmo\Core\Plugin::getInstance()->init();
}

add_action('plugins_loaded', 'UpImmo\init_plugin'); 