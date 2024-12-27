<?php
namespace UpImmo\Import;

use UpImmo\Core\Singleton;
use UpImmo\Import\Strategies\CSVImportStrategy;

class ImportManager extends Singleton {
    private const OPTION_NAME = 'up_immo_import_path';
    private $importContext;
    private $strategy;

    public function __construct() {
        $this->importContext = new ImportContext();
        add_action('wp_ajax_up_immo_import', [$this, 'handleImport']);
    }

    public function setStrategy($strategy): void {
        $this->strategy = $strategy;
    }

    public function handleImport(): void {
        if (!$this->strategy) {
            throw new \RuntimeException('No import strategy set');
        }
        
        $filePath = $this->getLastImportPath();
        if (empty($filePath)) {
            throw new \RuntimeException('No import path set');
        }
        
        $this->strategy->import($filePath);
    }

    public function getLastImportPath(): string {
        return get_option(self::OPTION_NAME, '');
    }
} 