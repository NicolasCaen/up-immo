<?php
namespace UpImmo\Import\Strategies;

use UpImmo\Import\Interfaces\ImportStrategyInterface;
use UpImmo\Helpers\ZipHelper;
use UpImmo\Filters\ContentFilters;

class CSVImportStrategy implements ImportStrategyInterface {
    protected $context;
    protected $progress = [];
    protected $encoding = 'ISO-8859-1';
    protected $zipHelper;
    protected $logs = [];
    protected $contentFilters;

    public function __construct($context = null) {
        $this->context = $context;
        $this->zipHelper = new ZipHelper();
        $this->contentFilters = new ContentFilters($this->encoding);
    }

    public function setEncoding(string $encoding): void {
        $this->encoding = $encoding;
    }

    public function readData(string $filePath): array {
        $this->addLog('Vérification du type de chemin : ' . $filePath);
        
        if (strpos($filePath, '/') !== 0 || strpos($filePath, ':') === false) {
            $fullPath = trailingslashit(WP_CONTENT_DIR) . ltrim($filePath, '/');
            $this->addLog('Chemin complet construit : ' . $fullPath);
        } else {
            $fullPath = $filePath;
        }

        if (is_dir($fullPath)) {
            $this->addLog('Recherche dans le dossier : ' . $fullPath);
            try {
                $csvPath = $this->zipHelper->extractCsvFromZip($fullPath);
                $this->addLog('CSV extrait : ' . $csvPath);
                return $this->readCSV(WP_CONTENT_DIR . $csvPath);
            } catch (\Exception $e) {
                $this->addLog('Erreur extraction : ' . $e->getMessage());
                throw $e;
            }
        }
        
        return $this->readCSV($fullPath);
    }

    public function importRow(array $row): array {
        try {
            $mapped_data = $this->mapData($row);
            $this->addLog("Traitement du bien : {$mapped_data['reference']}");
            // $this->addLog("description : {$mapped_data['description']}");
            
            $post_id = $this->createPost($mapped_data);
            $this->addLog("Post créé/mis à jour avec ID : {$post_id}");
            
            $this->updateTaxonomies($post_id, $row);
            $this->addLog("Taxonomies mises à jour");
            
            $this->importImages($post_id, $mapped_data['images']);
            $this->addLog("Images importées");
            
            return [
                'success' => true,
                'message' => "Bien {$mapped_data['reference']} importé avec succès",
                'post_id' => $post_id
            ];
        } catch (\Exception $e) {
            $this->addLog("Erreur : " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'post_id' => null
            ];
        }
    }

    public function import(string $filePath): array {
        // Vider les logs au début d'un nouvel import
        $this->clearLogs();
        
        $rows = $this->readData($filePath);
        $results = [];

        foreach ($rows as $row) {
            $results[] = $this->importRow($row);
        }

        // Ajouter un log de fin d'import
        $this->addLog("Import terminé - " . count($results) . " biens traités");
        
        // Optionnel : vider les logs après un délai
        wp_schedule_single_event(time() + 300, 'up_immo_clear_logs'); // Nettoie après 5 minutes

        return $results;
    }

    protected function readCSV(string $filePath): array {
        $this->addLog('Lecture du CSV : ' . $filePath);

        if (!file_exists($filePath)) {
            throw new \Exception('Fichier introuvable : ' . $filePath);
        }

        // Lire le contenu du fichier
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception('Impossible de lire le fichier');
        }

        // Détecter l'encodage réel du fichier
        $detected_encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'Windows-1252'], true);
        $this->addLog('Encodage détecté : ' . ($detected_encoding ?: 'inconnu'));

        // Convertir en UTF-8 avec une méthode plus robuste
        if ($detected_encoding && $detected_encoding !== 'UTF-8') {
            // Première tentative avec l'encodage détecté
            $content = iconv($detected_encoding, 'UTF-8//TRANSLIT//IGNORE', $content);
        } else {
            // Tentative avec Windows-1252 si la détection a échoué
            $content = iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $content);
        }

        // Remplacer le séparateur !# par un caractère unique
        $content = str_replace('!#', '|', $content);
        
        // Créer un fichier temporaire
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_');
        if (!$tmpFile) {
            throw new \Exception('Impossible de créer le fichier temporaire');
        }

        // Écrire le contenu converti avec BOM UTF-8
        $content = "\xEF\xBB\xBF" . $content; // Ajouter BOM UTF-8
        if (file_put_contents($tmpFile, $content) === false) {
            unlink($tmpFile);
            throw new \Exception('Impossible d\'écrire dans le fichier temporaire');
        }

        // Lire le CSV avec le nouveau séparateur
        $handle = fopen($tmpFile, 'r');
        if (!$handle) {
            unlink($tmpFile);
            throw new \Exception('Impossible d\'ouvrir le fichier temporaire');
        }

        // Définir l'encodage pour fgetcsv
        setlocale(LC_ALL, 'fr_FR.UTF-8');

        $rows = [];
        while (($data = fgetcsv($handle, 0, '|')) !== false) {
            // Nettoyer chaque valeur
            $data = array_map(function($value) {
                return trim($value);
            }, $data);
            $rows[] = $data;
        }

        // Nettoyage
        fclose($handle);
        unlink($tmpFile);

        $this->addLog('Nombre de lignes lues : ' . count($rows));
        return $rows;
    }

    protected function mapData(array $row): array {
        // Récupérer et décoder le mapping
        $mapping = get_option('up_immo_mapping_json');
        if (empty($mapping)) {
            throw new \Exception('Configuration du mapping manquante');
        }
    
        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Configuration du mapping invalide');
            }
        }
    
        $mapped_data = [];
        foreach ($mapping as $field => $index) {
            $value = $row[$index] ?? '';
            $mapped_data[$field] = ContentFilters::applyFilters($value, $field);
        }
    
        // Ajouter les images
        $mapped_data['images'] = $this->extractImages($row);
    
        return $mapped_data;
    }

    protected function extractImages(array $row): array {
        $images = [];
        // Images principales (84-92)
        for ($i = 84; $i <= 92; $i++) {
            if (!empty($row[$i])) {
                $images[] = esc_url_raw($row[$i]);
            }
        }
        // Images supplémentaires (163-167)
        for ($i = 163; $i <= 167; $i++) {
            if (!empty($row[$i])) {
                $images[] = esc_url_raw($row[$i]);
            }
        }
        return $images;
    }

    protected function createPost(array $data): int {
        try {
            // Vérifier les données requises
            if (empty($data['reference'])) {
                throw new \Exception('Référence manquante');
            }

            $this->addLog("Début création/mise à jour du bien : " . $data['reference']);

            // Rechercher si le bien existe déjà par sa référence
            $existing_posts = get_posts([
                'post_type' => 'bien',
                'meta_key' => 'reference',
                'meta_value' => $data['reference'],
                'posts_per_page' => 1,
            ]);

            $post_data = [
                'post_title' => $data['reference'] ?? $data['titre'],
                'post_content' => $data['titre'] ?? '',
                'post_excerpt' => $data['excerpt'] ?? $data['description'],
                'post_status' => 'publish',
                'post_type' => 'bien'
            ];

            if (!empty($existing_posts)) {
                $this->addLog("Bien existant trouvé avec ID : " . $existing_posts[0]->ID);
                $post_data['ID'] = $existing_posts[0]->ID;
                $post_id = wp_update_post($post_data, true);
            } else {
                $this->addLog("Création d'un nouveau bien");
                $post_id = wp_insert_post($post_data, true);
            }

            if (is_wp_error($post_id)) {
                throw new \Exception($post_id->get_error_message());
            }

            // Définir la première image comme thumbnail si elle existe
            if (!empty($data['images'][0])) {
                // Télécharger l'image et la définir comme thumbnail
                $this->setFeaturedImage($post_id, $data['images'][0]);
            }

            $this->addLog("Post créé/mis à jour avec ID : " . $post_id);

            // Mettre à jour les meta données
            foreach ($data as $key => $value) {
                if (!in_array($key, ['images', 'titre', 'description', 'excerpt'])) {
                    $this->addLog("Mise à jour meta '$key' avec valeur : " . (is_array($value) ? json_encode($value) : $value));
                    update_post_meta($post_id, $key, $value);
                }
            }

            return $post_id;

        } catch (\Exception $e) {
            $this->addLog("ERREUR dans createPost : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Télécharge une image et la définit comme thumbnail du post
     */
    protected function setFeaturedImage(int $post_id, string $image_url): void {
        // Vérifier si l'URL est valide
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            return;
        }

        // Télécharger l'image
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Télécharger l'image dans la bibliothèque de médias
        $attachment_id = media_sideload_image($image_url, $post_id, '', 'id');

        if (!is_wp_error($attachment_id)) {
            // Définir l'image comme thumbnail du post
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    private function updateTaxonomies(int $post_id, array $data): void {
        // Type de bien
        $type_term = term_exists(mb_convert_encoding($data[3] ?? '', 'UTF-8', $this->encoding), 'type_de_bien');
        if (!$type_term) {
            $type_term = wp_insert_term(
                mb_convert_encoding($data[3] ?? '', 'UTF-8', $this->encoding),
                'type_de_bien'
            );
        }
        if (!is_wp_error($type_term)) {
            wp_set_object_terms($post_id, (int)$type_term['term_id'], 'type_de_bien');
        }

        // Ville
        $ville_term = term_exists(mb_convert_encoding($data[5] ?? '', 'UTF-8', $this->encoding), 'ville');
        if (!$ville_term) {
            $ville_term = wp_insert_term(
                mb_convert_encoding($data[5] ?? '', 'UTF-8', $this->encoding),
                'ville'
            );
        }
        if (!is_wp_error($ville_term)) {
            wp_set_object_terms($post_id, (int)$ville_term['term_id'], 'ville');
        }

        // Disponibilité
        $dispo = ($data[12] ?? '') === 'NON' ? 'Disponible' : 'Vendu';
        $dispo_term = term_exists($dispo, 'disponibilite');
        if (!$dispo_term) {
            $dispo_term = wp_insert_term($dispo, 'disponibilite');
        }
        if (!is_wp_error($dispo_term)) {
            wp_set_object_terms($post_id, (int)$dispo_term['term_id'], 'disponibilite');
        }
    }

    private function importImages(int $post_id, array $image_urls): void {
        $existing_images = $this->getExistingImages($post_id);
        $this->addLog("Début traitement des images - " . count($image_urls) . " images à traiter");
        
        foreach ($image_urls as $index => $image_url) {
            if (empty($image_url)) {
                $this->addLog("Image " . ($index + 1) . " : URL vide, ignorée");
                continue;
            }

            // Vérifier si l'URL existe déjà
            $existing_attachment_id = array_search($image_url, $existing_images);
            
            if ($existing_attachment_id !== false) {
                $this->addLog("Image " . ($index + 1) . " : Déjà existante (ID: " . $existing_attachment_id . ")");
                // L'image existe déjà, vérifier si elle a changé
                $this->updateImageIfNeeded($existing_attachment_id, $image_url);
                continue;
            }

            try {
                $attachment_id = $this->importImage($post_id, $image_url);
                $this->addLog("Image " . ($index + 1) . " : Importée avec succès (ID: " . $attachment_id . ")");
            } catch (\Exception $e) {
                $this->addLog("Image " . ($index + 1) . " : Erreur d'import - " . $e->getMessage());
            }
        }

        $this->addLog("Fin du traitement des images");
    }

    private function importImage(int $post_id, string $image_url): int|false {
        // Télécharger l'image
        $tmp_file = download_url($image_url);
        if (is_wp_error($tmp_file)) {
            throw new \Exception('Erreur téléchargement : ' . $tmp_file->get_error_message());
        }

        $this->addLog("Image téléchargée temporairement : " . basename($image_url));

        // Préparer le fichier pour l'import
        $file_array = [
            'name' => basename($image_url),
            'tmp_name' => $tmp_file
        ];

        // Ne pas vérifier le type MIME pour les images distantes
        add_filter('upload_mimes', function($mimes) {
            $mimes['jpg|jpeg|jpe'] = 'image/jpeg';
            return $mimes;
        });

        // Désactiver le contrôle du type de fichier
        add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
            $filetype = wp_check_filetype($filename);
            return [
                'ext' => $filetype['ext'],
                'type' => $filetype['type'],
                'proper_filename' => $filename
            ];
        }, 10, 4);

        // Importer l'image
        $attachment_id = media_handle_sideload($file_array, $post_id);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp_file);
            throw new \Exception('Erreur import : ' . $attachment_id->get_error_message());
        }

        // Sauvegarder l'URL source
        update_post_meta($attachment_id, '_source_url', $image_url);

        return $attachment_id;
    }

    private function updateImageIfNeeded(int $attachment_id, string $image_url): void {
        // Vérifier si l'image distante a changé
        $tmp_file = download_url($image_url);
        if (is_wp_error($tmp_file)) {
            $this->addLog("Impossible de télécharger l'image pour comparaison : " . $image_url);
            return;
        }

        $local_file = get_attached_file($attachment_id);
        if (!$local_file) {
            $this->addLog("Fichier local introuvable pour l'image ID: " . $attachment_id);
            unlink($tmp_file);
            return;
        }

        // Comparer les fichiers
        if (filesize($tmp_file) !== filesize($local_file) || 
            md5_file($tmp_file) !== md5_file($local_file)) {
            
            $this->addLog("Mise à jour de l'image ID: " . $attachment_id . " (contenu modifié)");
            
            // Mettre à jour l'image
            $file_array = [
                'name' => basename($image_url),
                'tmp_name' => $tmp_file
            ];

            $new_attachment_id = media_handle_sideload($file_array, $attachment_id);
            if (!is_wp_error($new_attachment_id)) {
                update_post_meta($new_attachment_id, '_source_url', $image_url);
                $this->addLog("Image mise à jour avec succès (nouvel ID: " . $new_attachment_id . ")");
            } else {
                $this->addLog("Erreur lors de la mise à jour de l'image : " . $new_attachment_id->get_error_message());
            }
        } else {
            $this->addLog("Image ID: " . $attachment_id . " inchangée, pas de mise à jour nécessaire");
        }

        unlink($tmp_file);
    }

    private function getExistingImages(int $post_id): array {
        $args = [
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'meta_key' => '_source_url',
            'posts_per_page' => -1
        ];
        
        $existing = get_posts($args);
        $images = [];
        foreach ($existing as $post) {
            $source_url = get_post_meta($post->ID, '_source_url', true);
            if ($source_url) {
                $images[] = $source_url;
            }
        }
        return $images;
    }

    public function getProgress(): array {
        return $this->progress;
    }

    protected function addLog(string $message): void {
        $this->logs[] = [
            'time' => current_time('mysql'),
            'message' => $message
        ];
        
        // Stocker les logs en option WordPress
        update_option('up_immo_import_logs', $this->logs, false);
    }

    protected function clearLogs(): void {
        $this->logs = [];
        delete_option('up_immo_import_logs');
    }

    public function __destruct() {
        // Nettoyer les filtres à la fin
        if ($this->contentFilters) {
            $this->contentFilters->removeFilters();
        }
    }
} 