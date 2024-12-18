<?php
namespace UpImmo\Helpers;

class ZipHelper {
    public static function extractCsvFromZip(string $zip_path): ?string {
        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Début extraction ZIP : ' . $zip_path);
        }

        $full_zip_path = WP_CONTENT_DIR . $zip_path;
        
        if (!file_exists($full_zip_path)) {
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Fichier ZIP non trouvé : ' . $full_zip_path);
            }
            throw new \Exception("Fichier ZIP non trouvé : " . $full_zip_path);
        }

        $zip = new \ZipArchive();
        if ($zip->open($full_zip_path) !== true) {
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Impossible d\'ouvrir le ZIP : ' . $full_zip_path);
            }
            throw new \Exception("Impossible d'ouvrir le ZIP");
        }

        // Créer un dossier temporaire
        $temp_dir = WP_CONTENT_DIR . '/uploads/up-immo-temp/';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        // Extraire et chercher le fichier CSV
        $csv_path = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Fichier trouvé dans ZIP : ' . $filename);
            }
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                $zip->extractTo($temp_dir, $filename);
                $csv_path = $temp_dir . $filename;
                break;
            }
        }

        $zip->close();

        if ($csv_path === null) {
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Aucun fichier CSV trouvé dans le ZIP');
            }
            throw new \Exception("Aucun fichier CSV trouvé dans le ZIP");
        }

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - CSV extrait avec succès : ' . $csv_path);
        }

        return str_replace(WP_CONTENT_DIR, '', $csv_path);
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