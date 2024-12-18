<?php
namespace UpImmo\Import\Interfaces;

interface ImportStrategyInterface {
    public function import(string $file_path): array;
    public function validate(string $file_path): bool;
    public function getProgress(): array;
} 