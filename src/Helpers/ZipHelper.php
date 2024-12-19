<?php
namespace UpImmo\Helpers;

class ZipHelper {
    public static function extractCsvFromZip(string $path): ?string {
        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Début extraction ZIP depuis : ' . $path);
        }

        // Chercher le ZIP dans le dossier
        $zipFiles = glob($path . '/*.zip');
        if (empty($zipFiles)) {
            throw new \Exception('Aucun fichier ZIP trouvé dans : ' . $path);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFiles[0]) !== true) {
            throw new \Exception("Impossible d'ouvrir le ZIP : " . $zipFiles[0]);
        }

        // Créer un dossier temporaire
        $temp_dir = WP_CONTENT_DIR . '/uploads/up-immo-temp/';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        // Extraire et chercher le fichier CSV
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                $zip->extractTo($temp_dir, $filename);
                $csvPath = $temp_dir . $filename;
                $zip->close();
                return str_replace(WP_CONTENT_DIR, '', $csvPath);
            }
        }

        $zip->close();
        throw new \Exception('Aucun fichier CSV trouvé dans le ZIP');
    }

    public static function cleanupTemp(): void {
        $temp_dir = WP_CONTENT_DIR . '/uploads/up-immo-temp/';
        if (file_exists($temp_dir)) {
            self::deleteDirectory($temp_dir);
        }
    }

    private static function deleteDirectory($dir): void {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            (is_dir($path)) ? self::deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
} 