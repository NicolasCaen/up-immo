<?php
namespace UpImmo\Import\Strategies;

use UpImmo\Import\Interfaces\ImportStrategyInterface;
use UpImmo\Helpers\ZipHelper;

class CSVImportStrategy implements ImportStrategyInterface {
    protected $context;
    protected $progress = [];
    protected $encoding = 'ISO-8859-1';
    protected $zipHelper;

    public function __construct($context = null) {
        $this->context = $context;
        $this->zipHelper = new ZipHelper();
    }

    public function setEncoding(string $encoding): void {
        $this->encoding = $encoding;
    }

    public function readData(string $filePath): array {
        error_log('UP_IMMO - Vérification du type de chemin : ' . $filePath);
        
        // Construire le chemin complet si ne commence pas par /
        if (strpos($filePath, '/') !== 0 || strpos($filePath, ':') === false) {
            $fullPath = trailingslashit(WP_CONTENT_DIR) . ltrim($filePath, '/');
            error_log('UP_IMMO - Chemin complet construit : ' . $fullPath);
        } else {
            $fullPath = $filePath;
        }

        // Vérifier si c'est un dossier contenant un ZIP
        if (is_dir($fullPath)) {
            error_log('UP_IMMO - Recherche dans le dossier : ' . $fullPath);
            try {
                $csvPath = $this->zipHelper->extractCsvFromZip($fullPath);
                error_log('UP_IMMO - CSV extrait : ' . $csvPath);
                return $this->readCSV(WP_CONTENT_DIR . $csvPath);
            } catch (\Exception $e) {
                error_log('UP_IMMO - Erreur extraction : ' . $e->getMessage());
                throw $e;
            }
        }
        
        return $this->readCSV($fullPath);
    }

    public function importRow(array $row): array {
        try {
            $mapped_data = $this->mapData($row);
            $post_id = $this->createPost($mapped_data);
            $this->updateTaxonomies($post_id, $row);
            $this->importImages($post_id, $mapped_data['images']);
            
            return [
                'success' => true,
                'message' => "Bien {$mapped_data['reference']} importé avec succès",
                'post_id' => $post_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'post_id' => null
            ];
        }
    }

    public function import(string $filePath): array {
        $rows = $this->readData($filePath);
        $results = [];

        foreach ($rows as $row) {
            $results[] = $this->importRow($row);
        }

        return $results;
    }

    protected function readCSV(string $filePath): array {
        error_log('UP_IMMO - Lecture du CSV : ' . $filePath);

        if (!file_exists($filePath)) {
            throw new \Exception('Fichier introuvable : ' . $filePath);
        }

        // Lire le contenu du fichier
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception('Impossible de lire le fichier');
        }

        // Convertir l'encodage si nécessaire
        if ($this->encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $this->encoding);
        }

        // Remplacer le séparateur !# par un caractère unique (par exemple |)
        $content = str_replace('!#', '|', $content);
        
        // Créer un fichier temporaire
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_');
        if (!$tmpFile) {
            throw new \Exception('Impossible de créer le fichier temporaire');
        }

        // Écrire le contenu modifié
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

        $rows = [];
        while (($data = fgetcsv($handle, 0, '|')) !== false) {
            $rows[] = array_map('trim', $data);
        }

        // Nettoyage
        fclose($handle);
        unlink($tmpFile);

        error_log('UP_IMMO - Nombre de lignes lues : ' . count($rows));
        return $rows;
    }

    protected function mapData(array $row): array {
        // Récupérer le mapping depuis les options
        $mapping = get_option('up_immo_mapping_json');
        if (empty($mapping)) {
            error_log('UP_IMMO - Erreur : Mapping non configuré');
            throw new \Exception('Configuration du mapping manquante');
        }

        // Décoder le JSON si nécessaire
        if (is_string($mapping)) {
            $mapping = json_decode($mapping, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('UP_IMMO - Erreur : JSON mapping invalide');
                throw new \Exception('Configuration du mapping invalide');
            }
        }

        // Fonction helper pour nettoyer et convertir l'encodage
        $cleanAndConvert = function($value) {
            if (empty($value)) return '';
            
            // Convertir l'encodage
            $value = $this->encoding !== 'UTF-8' 
                ? mb_convert_encoding($value, 'UTF-8', $this->encoding) 
                : $value;
            
            // Remplacer les caractères problématiques
            $search = [
                chr(233), chr(232), chr(224), chr(234), chr(244), 
                chr(238), chr(251), chr(231), chr(226), chr(235),
                chr(239), chr(249), chr(252), chr(228), chr(246),
                chr(171), chr(187)
            ];
            $replace = [
                'é', 'è', 'à', 'ê', 'ô', 
                'î', 'û', 'ç', 'â', 'ë',
                'ï', 'ù', 'ü', 'ä', 'ö',
                '"', '"'
            ];
            $value = str_replace($search, $replace, $value);
            
            // Nettoyer les espaces multiples et les retours à la ligne
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);
            
            return $value;
        };

        $mapped_data = [];
        foreach ($mapping as $field => $index) {
            $value = $row[$index] ?? '';
            
            // Appliquer les transformations nécessaires selon le champ
            switch ($field) {
                case 'description':
                    // Convertir les <br> en retours à la ligne avant nettoyage
                    $value = str_replace('<br>', "\n", $value);
                    $value = $cleanAndConvert($value);
                    // Restaurer les <br> après nettoyage
                    $value = str_replace("\n", '<br>', $value);
                    $mapped_data[$field] = wp_kses_post($value);
                    break;
                case 'contact_email':
                    $mapped_data[$field] = sanitize_email($value);
                    break;
                default:
                    $mapped_data[$field] = sanitize_text_field($cleanAndConvert($value));
            }
        }

        // Ajouter les images si présentes
        $mapped_data['images'] = $this->extractImages($row);

        error_log('UP_IMMO - Données mappées : ' . print_r($mapped_data, true));
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
        // Vérifier les données requises
        if (empty($data['titre']) && empty($data['reference'])) {
            throw new \Exception('Titre ou référence manquant');
        }

        $post_id = wp_insert_post([
            'post_title' => $data['reference'] ?? $data['titre'],
            'post_content' => $data['titre'] ?? '',
            'post_excerpt' => $data['excerpt'] ?? $data['description'],
            'post_status' => 'publish',
            'post_type' => 'bien'
        ]);

        if (!$post_id || is_wp_error($post_id)) {
            throw new \Exception('Erreur lors de la création du bien');
        }

        // Mettre à jour les meta données
        foreach ($data as $key => $value) {
            if ($key !== 'images' && $key !== 'titre' && $key !== 'description') {
                update_post_meta($post_id, $key, $value);
            }
        }

        return $post_id;
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
        foreach ($image_urls as $image_url) {
            if (empty($image_url)) continue;

            // Vérifier si l'image existe déjà
            if ($this->imageExists($post_id, $image_url)) continue;

            try {
                $this->importImage($post_id, $image_url);
            } catch (\Exception $e) {
                error_log('UP_IMMO - Erreur import image : ' . $e->getMessage());
            }
        }
    }

    private function importImage(int $post_id, string $image_url): int|false {
        // Télécharger l'image
        $tmp_file = download_url($image_url);
        if (is_wp_error($tmp_file)) {
            throw new \Exception('Erreur téléchargement : ' . $tmp_file->get_error_message());
        }

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

    private function imageExists(int $post_id, string $image_url): bool {
        $args = [
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'meta_key' => '_source_url',
            'meta_value' => $image_url,
            'posts_per_page' => 1
        ];
        
        $existing = get_posts($args);
        return !empty($existing);
    }

    public function getProgress(): array {
        return $this->progress;
    }
} 