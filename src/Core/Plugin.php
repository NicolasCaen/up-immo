<?php
namespace UpImmo\Core;

use UpImmo\Admin\AdminPage;
use UpImmo\Admin\AdminAjax;
use UpImmo\Admin\SettingsPage;
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
            $this->admin = new \UpImmo\Admin\AdminPage();
            $this->adminAjax = new \UpImmo\Admin\AdminAjax();
            new \UpImmo\Admin\SettingsPage();
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
        add_action('before_delete_post', [$this, 'deleteImagesWithBien']);
    }

    public function registerPostTypes(): void {
        // Registration logic for post types
    }

    public function registerTaxonomies(): void {
        // Registration logic for taxonomies
    }

    public function deleteImagesWithBien($post_id) {
        if (get_post_type($post_id) !== 'bien') {
            return;
        }

        if (!get_option('up_immo_delete_images_with_bien', 0)) {
            return;
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'posts_per_page' => -1
        ]);

        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true);
        }
    }
} 