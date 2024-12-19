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

    public function import(string $filePath): array {
        error_log('UP_IMMO - Début import avec stratégie: ' . ($this->strategy ? get_class($this->strategy) : 'aucune'));
        error_log('UP_IMMO - Chemin du fichier: ' . $filePath);

        if (!$this->strategy) {
            error_log('UP_IMMO - Erreur : Aucune stratégie définie');
            return [[
                'success' => false,
                'message' => 'Aucune stratégie d\'import définie'
            ]];
        }

        try {
            $results = $this->strategy->import($filePath);
            
            // Traiter les résultats pour s'assurer qu'ils sont dans le bon format
            $processed_results = [];
            foreach ($results as $result) {
                if (isset($result['success'])) {
                    $processed_results[] = [
                        'success' => $result['success'],
                        'message' => $result['message'] ?? '',
                        'post_id' => $result['post_id'] ?? null
                    ];
                }
            }
            
            return $processed_results;
            
        } catch (\Exception $e) {
            error_log('UP_IMMO - Erreur import : ' . $e->getMessage());
            return [[
                'success' => false,
                'message' => $e->getMessage()
            ]];
        }
    }

    public function getProgress(): array {
        if (!$this->strategy) {
            return ['percentage' => 0, 'current' => 0, 'total' => 0];
        }
        return $this->strategy->getProgress();
    }
} 