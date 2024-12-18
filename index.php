<?php
/**
 * Plugin Name: UP Immo
 * Description: Plugin de gestion immobilière
 * Version: 1.0
 * Author: GEHIN Nicolas
 */

namespace UpImmo;

if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('UP_IMMO_PATH', plugin_dir_path(__FILE__));
define('UP_IMMO_URL', plugin_dir_url(__FILE__));

// Custom Autoloader
function autoloader($class) {
    // Vérifier si la classe appartient à notre namespace
    $prefix = 'UpImmo\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Récupérer le chemin relatif de la classe
    $relative_class = substr($class, $len);
    
    // Convertir le namespace en chemin de fichier
    $file = UP_IMMO_PATH . 'src/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si le fichier existe, on l'inclut
    if (file_exists($file)) {
        require $file;
    }
}

// Enregistrer l'autoloader
spl_autoload_register('UpImmo\autoloader');

// Initialize plugin
Core\Plugin::getInstance()->init(); 