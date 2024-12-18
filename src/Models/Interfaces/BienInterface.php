<?php
namespace UpImmo\Models\Interfaces;

interface BienInterface {
    public function getId(): int;
    public function getTitle(): string;
    public function getPrice(): float;
    public function getDescription(): string;
    public function getImages(): array;
    public function getMetaData(): array;
} 