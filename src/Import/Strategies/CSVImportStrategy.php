<?php
namespace UpImmo\Import\Strategies;

use UpImmo\Import\Interfaces\ImportStrategyInterface;

class CSVImportStrategy implements ImportStrategyInterface {
    private $progress = [
        'total' => 0,
        'current' => 0,
        'percentage' => 0
    ];

    public function import(string $file_path): array {
        if (!$this->validate($file_path)) {
            throw new \Exception("Invalid CSV file");
        }

        $results = [];
        $handle = fopen($file_path, "r");
        $headers = fgetcsv($handle, 1000, ",");
        
        $this->progress['total'] = count(file($file_path)) - 1;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $bien_data = array_combine($headers, $data);
            $results[] = $this->createOrUpdateBien($bien_data);
            
            $this->progress['current']++;
            $this->progress['percentage'] = ($this->progress['current'] / $this->progress['total']) * 100;
        }

        fclose($handle);
        return $results;
    }

    public function validate(string $file_path): bool {
        return file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'csv';
    }

    public function getProgress(): array {
        return $this->progress;
    }

    private function createOrUpdateBien(array $data): int {
        $post_data = [
            'post_type' => 'bien',
            'post_title' => $data['titre'] ?? '',
            'post_content' => $data['description'] ?? '',
            'post_status' => 'publish'
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            // Update meta fields
            update_post_meta($post_id, '_price', $data['prix'] ?? 0);
            
            // Handle image import if URL is provided
            if (!empty($data['image_url'])) {
                $this->importImage($post_id, $data['image_url']);
            }
        }

        return $post_id;
    }

    private function importImage(int $post_id, string $image_url): void {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $image_id = media_sideload_image($image_url, $post_id, '', 'id');
        if (!is_wp_error($image_id)) {
            set_post_thumbnail($post_id, $image_id);
        }
    }
} 