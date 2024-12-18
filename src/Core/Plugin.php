<?php
namespace UpImmo\Core;

class Plugin extends Singleton {
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