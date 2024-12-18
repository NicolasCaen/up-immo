<?php
namespace UpImmo\Import;

use UpImmo\Import\Interfaces\ImportStrategyInterface;

class ImportContext {
    private $strategy;

    public function setStrategy(ImportStrategyInterface $strategy): void {
        $this->strategy = $strategy;
    }

    public function import(string $file_path): array {
        if (!$this->strategy) {
            throw new \Exception("No import strategy set");
        }
        return $this->strategy->import($file_path);
    }

    public function getProgress(): array {
        if (!$this->strategy) {
            return ['percentage' => 0, 'current' => 0, 'total' => 0];
        }
        return $this->strategy->getProgress();
    }
} 