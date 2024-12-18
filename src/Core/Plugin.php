<?php
namespace UpImmo\Core;

use UpImmo\Admin\AdminPage;
use UpImmo\Admin\AdminAjax;
use UpImmo\Import\ImportManager;

class Plugin extends Singleton {
    private static $instance = null;
    private $admin = null;
    private $adminAjax = null;

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
            add_action('admin_enqueue_scripts', [$this->admin, 'enqueueScripts']);
            $this->adminAjax = new AdminAjax();
        }

        // Initialiser le reste du plugin
        $this->init();
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