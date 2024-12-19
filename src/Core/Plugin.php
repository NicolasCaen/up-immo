<?php
namespace UpImmo\Core;

use UpImmo\Admin\AdminPage;
use UpImmo\Admin\AdminAjax;
use UpImmo\Admin\SettingsPage;
use UpImmo\Admin\ImportPage;
use UpImmo\Import\ImportManager;

class Plugin extends Singleton {
    private static $instance = null;
    private $admin = null;
    private $adminAjax = null;
    private $settings;

    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        // Initialiser les composants admin
        if (is_admin()) {
            $this->admin = new AdminPage();
            $this->settings = new \UpImmo\Admin\SettingsPage();
            new \UpImmo\Admin\ImportPage();
            add_action('admin_enqueue_scripts', [$this->admin, 'enqueueScripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStyles']);
            $this->adminAjax = new AdminAjax();
        }

        // Initialiser le reste du plugin
        $this->init();
    }
    public function enqueueAdminStyles($hook): void {
        // Liste des pages où charger le CSS
        $allowed_pages = [
            'post.php',
            'post-new.php',
            'edit.php',
            'toplevel_page_up-immo-settings'
        ];

        // Vérifier si nous sommes sur le type de post 'bien'
        $screen = get_current_screen();

        wp_enqueue_style(
            'up-immo-admin',
            plugins_url('/assets/css/admin.css', dirname(dirname(__FILE__))),
            [],
            filemtime(plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/css/admin.css')
        );
    }
    public function init(): void {
        // Initialize Post Types
        new \UpImmo\PostTypes\BienPostType();

        // Initialize Taxonomies
        new \UpImmo\Taxonomies\TypeDeBienTaxonomy();
        new \UpImmo\Taxonomies\VilleTaxonomy();
        new \UpImmo\Taxonomies\EtatTaxonomy();
        new \UpImmo\Taxonomies\DisponibiliteTaxonomy();

        // Initialize Import Manager
        \UpImmo\Import\ImportManager::getInstance();

        // Add hooks
        add_action('init', [$this, 'registerPostTypes']);
        add_action('init', [$this, 'registerTaxonomies']);
    }

    public function registerPostTypes(): void {
        // Registration logic for post types
    }

    public function registerTaxonomies(): void {
        // Registration logic for taxonomies
    }
} 