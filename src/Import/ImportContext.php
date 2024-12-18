<?php
namespace UpImmo\Import;

use UpImmo\Import\Interfaces\ImportStrategyInterface;
use UpImmo\Import\Strategies\CSVImportStrategy;

class ImportContext {
    private $strategy;

    public function __construct() {
        $this->strategy = new CSVImportStrategy();
    }

    public function setStrategy(ImportStrategyInterface $strategy): void {
        $this->strategy = $strategy;
    }

    public function import(string $path): array {
        if (!$this->strategy) {
            throw new \Exception('No import strategy set');
        }

        return $this->strategy->import($path);
    }

    public function getProgress(): array {
        if (!$this->strategy) {
            return ['percentage' => 0, 'current' => 0, 'total' => 0];
        }
        return $this->strategy->getProgress();
    }
} 