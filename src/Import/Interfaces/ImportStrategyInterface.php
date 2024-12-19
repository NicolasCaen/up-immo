<?php
namespace UpImmo\Import\Interfaces;

interface ImportStrategyInterface {
    public function importRow(array $row);
    public function readData(string $filePath): array;
    public function getProgress(): array;
    public function setEncoding(string $encoding): void;
} 