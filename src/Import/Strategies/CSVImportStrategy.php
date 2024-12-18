<?php
namespace UpImmo\Import\Strategies;

use UpImmo\Import\Interfaces\ImportStrategyInterface;

class CSVImportStrategy implements ImportStrategyInterface {
    private $progress = [
        'total' => 0,
        'current' => 0,
        'percentage' => 0
    ];

    private function parseLine(string $line): array {
        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Parsing ligne : ' . $line);
        }

        // Diviser la ligne en utilisant le séparateur !#
        $parts = explode('!#', $line);
        
        // Nettoyer les guillemets
        $parts = array_map(function($part) {
            return trim($part, '"');
        }, $parts);

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Données parsées : ' . print_r($parts, true));
        }

        return $parts;
    }

    private function updateProgress($message, $percentage = null) {
        $this->progress['message'] = $message;
        if ($percentage !== null) {
            $this->progress['percentage'] = $percentage;
        }
        
        // Sauvegarder la progression dans une option temporaire
        update_option('up_immo_import_progress', [
            'message' => $message,
            'percentage' => $this->progress['percentage'],
            'timestamp' => time()
        ], false);

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Progress: ' . $message . ' (' . $this->progress['percentage'] . '%)');
        }
    }

    public function import(string $file_path): array {
        try {
            $this->sendProgressUpdate('Début de l\'import...', 0);
            
            // Construire le chemin complet
            $full_path = WP_CONTENT_DIR . $file_path;
            
            // Si c'est un dossier, chercher le fichier ZIP
            if (is_dir($full_path)) {
                $this->sendProgressUpdate('Recherche du fichier ZIP...', 5);
                if (DEBUG_UP_IMMO) {
                    error_log('UP_IMMO - Recherche de ZIP dans le dossier : ' . $full_path);
                }
                
                $files = scandir($full_path);
                $zip_file = null;
                
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                        $zip_file = $file;
                        if (DEBUG_UP_IMMO) {
                            error_log('UP_IMMO - Fichier ZIP trouvé : ' . $zip_file);
                        }
                        break;
                    }
                }
                
                if ($zip_file) {
                    $file_path = rtrim($file_path, '/') . '/' . $zip_file;
                    if (DEBUG_UP_IMMO) {
                        error_log('UP_IMMO - Chemin du ZIP : ' . $file_path);
                    }
                } else {
                    throw new \Exception("Aucun fichier ZIP trouvé dans le dossier : " . $full_path);
                }
            }

            $this->sendProgressUpdate('Extraction du ZIP...', 10);
            // Si c'est un ZIP, extraire le CSV
            if (pathinfo($file_path, PATHINFO_EXTENSION) === 'zip') {
                if (DEBUG_UP_IMMO) {
                    error_log('UP_IMMO - Fichier ZIP détecté, extraction...');
                }
                $file_path = \UpImmo\Helpers\ZipHelper::extractCsvFromZip($file_path);
            }

            $full_path = WP_CONTENT_DIR . $file_path;
            
            if (DEBUG_UP_IMMO) {
                error_log('UP_IMMO - Chemin final du fichier : ' . $full_path);
                error_log('UP_IMMO - Le fichier existe ? : ' . (file_exists($full_path) ? 'Oui' : 'Non'));
            }

            $results = [];

            if (!$this->validate($full_path)) {
                throw new \Exception("Fichier CSV invalide ou introuvable : " . $full_path);
            }

            $this->sendProgressUpdate('Lecture du fichier CSV...', 15);
            $handle = fopen($full_path, 'r');
            
            // Compter les lignes
            $total_lines = 0;
            while (!feof($handle)) {
                if (fgets($handle) !== false) $total_lines++;
            }
            rewind($handle);

            $current_line = 0;
            $this->sendProgressUpdate('Début du traitement des biens...', 20);

            while (($line = fgets($handle)) !== false) {
                $current_line++;
                $progress = 20 + (60 * ($current_line / $total_lines));
                
                try {
                    $data = $this->parseLine($line);
                    if ($data) {
                        $this->sendProgressUpdate(
                            sprintf('Import du bien %s (%d/%d)...', 
                                $data[1] ?? '',
                                $current_line, 
                                $total_lines
                            ),
                            $progress
                        );
                        
                        $post_id = $this->createOrUpdateBien($data);
                        $results[] = $post_id;
                    }
                } catch (\Exception $e) {
                    error_log('UP_IMMO - Erreur ligne ' . $current_line . ': ' . $e->getMessage());
                }
            }

            $this->sendProgressUpdate('Finalisation de l\'import...', 90);
            fclose($handle);
            
            $this->sendProgressUpdate('Import terminé !', 100);
            
            return [
                'success' => true,
                'message' => 'Import terminé avec succès',
                'imported' => $current_line,
                'total' => $total_lines
            ];

        } catch (\Exception $e) {
            $this->handleError($e);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } finally {
            \UpImmo\Helpers\ZipHelper::cleanupTemp();
        }
    }

    public function validate(string $file_path): bool {
        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Validation du fichier : ' . $file_path);
            error_log('UP_IMMO - Le fichier existe : ' . (file_exists($file_path) ? 'oui' : 'non'));
            error_log('UP_IMMO - Extension : ' . pathinfo($file_path, PATHINFO_EXTENSION));
            if (is_dir($file_path)) {
                error_log('UP_IMMO - C\'est un dossier');
            }
        }

        return file_exists($file_path) && 
               !is_dir($file_path) && 
               pathinfo($file_path, PATHINFO_EXTENSION) === 'csv';
    }

    public function getProgress(): array {
        return $this->progress;
    }

    private function createOrUpdateBien(array $data): int {
        // Mapper les données
        $mapped_data = [
            'reference' => sanitize_text_field($data[1] ?? ''),
            'titre' => sanitize_text_field(mb_convert_encoding($data[19] ?? '', 'UTF-8', 'ISO-8859-1')),
            'description' => wp_kses_post(mb_convert_encoding($data[20] ?? '', 'UTF-8', 'ISO-8859-1')),
            'prix' => sanitize_text_field($data[10] ?? ''),
            'surface' => sanitize_text_field($data[15] ?? ''),
            'pieces' => sanitize_text_field($data[17] ?? ''),
            'chambres' => sanitize_text_field($data[18] ?? ''),
            'code_postal' => sanitize_text_field($data[4] ?? ''),
            'ville' => sanitize_text_field(mb_convert_encoding($data[5] ?? '', 'UTF-8', 'ISO-8859-1')),
            'dpe' => sanitize_text_field($data[176] ?? ''),
            'contact_tel' => sanitize_text_field($data[104] ?? ''),
            'contact_email' => sanitize_email($data[106] ?? '')
        ];

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Données mappées : ' . print_r($mapped_data, true));
        }

        $post_data = [
            'post_type' => 'bien',
            'post_title' => $mapped_data['titre'],
            'post_excerpt' => $mapped_data['description'],
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        ];

        // Vérifier si un bien avec cette référence existe déjà
        $existing_posts = get_posts([
            'post_type' => 'bien',
            'meta_key' => 'reference',
            'meta_value' => $mapped_data['reference'],
            'posts_per_page' => 1
        ]);

        if (!empty($existing_posts)) {
            $post_data['ID'] = $existing_posts[0]->ID;
        }

        // Insérer ou mettre à jour le post
        $post_id = wp_insert_post($post_data);

        // Vérifier si l'insertion a réussi
        if (!$post_id || is_wp_error($post_id)) {
            $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Erreur inconnue';
            throw new \Exception('Erreur lors de la création du bien : ' . $error_message);
        }

        // Mettre à jour les meta données
        foreach ($mapped_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }

        // Mise à jour des taxonomies
        // Type de bien
        $type_term = term_exists(mb_convert_encoding($data[3] ?? '', 'UTF-8', 'ISO-8859-1'), 'type_de_bien');
        if (!$type_term) {
            $type_term = wp_insert_term(
                mb_convert_encoding($data[3] ?? '', 'UTF-8', 'ISO-8859-1'),
                'type_de_bien'
            );
        }
        if (!is_wp_error($type_term)) {
            wp_set_object_terms($post_id, (int)$type_term['term_id'], 'type_de_bien');
        }

        // Ville
        $ville_term = term_exists(mb_convert_encoding($data[5] ?? '', 'UTF-8', 'ISO-8859-1'), 'ville');
        if (!$ville_term) {
            $ville_term = wp_insert_term(
                mb_convert_encoding($data[5] ?? '', 'UTF-8', 'ISO-8859-1'),
                'ville'
            );
        }
        if (!is_wp_error($ville_term)) {
            wp_set_object_terms($post_id, (int)$ville_term['term_id'], 'ville');
        }

        // Disponibilité (basé sur le champ 12)
        $dispo = ($data[12] ?? '') === 'NON' ? 'Disponible' : 'Vendu';
        $dispo_term = term_exists($dispo, 'disponibilite');
        if (!$dispo_term) {
            $dispo_term = wp_insert_term($dispo, 'disponibilite');
        }
        if (!is_wp_error($dispo_term)) {
            wp_set_object_terms($post_id, (int)$dispo_term['term_id'], 'disponibilite');
        }

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Taxonomies mises à jour pour le bien ' . $post_id);
        }

        return $post_id;
    }

    private function imageExists($post_id, $image_url) {
        // Vérifier dans les métadonnées des images attachées
        $args = array(
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'meta_key' => '_source_url',
            'meta_value' => $image_url,
            'posts_per_page' => 1
        );
        
        $existing = get_posts($args);
        return !empty($existing);
    }

    private function importImage($post_id, $image_url) {
        try {
            if (empty($image_url)) {
                return false;
            }

            $this->updateProgress('Vérification de l\'image : ' . basename($image_url));

            if ($this->imageExists($post_id, $image_url)) {
                $this->updateProgress('Image déjà existante : ' . basename($image_url));
                return false;
            }

            $this->updateProgress('Import de l\'image : ' . basename($image_url));

            // Télécharger l'image
            $tmp_file = download_url($image_url);
            if (is_wp_error($tmp_file)) {
                error_log('UP_IMMO - Erreur téléchargement image : ' . $image_url . ' - ' . $tmp_file->get_error_message());
                return false;
            }

            // Préparer le fichier pour l'import
            $file_array = array(
                'name' => basename($image_url),
                'tmp_name' => $tmp_file
            );

            // Ne pas vérifier le type MIME pour les images distantes
            add_filter('upload_mimes', function($mimes) {
                $mimes['jpg|jpeg|jpe'] = 'image/jpeg';
                return $mimes;
            });

            // Désactiver le contrôle du type de fichier
            add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
                $filetype = wp_check_filetype($filename);
                return array(
                    'ext' => $filetype['ext'],
                    'type' => $filetype['type'],
                    'proper_filename' => $filename
                );
            }, 10, 4);

            // Importer l'image
            $attachment_id = media_handle_sideload($file_array, $post_id);

            if (is_wp_error($attachment_id)) {
                @unlink($tmp_file);
                error_log('UP_IMMO - Erreur import image : ' . $image_url . ' - ' . $attachment_id->get_error_message());
                return false;
            }

            // Sauvegarder l'URL source pour éviter les doublons
            update_post_meta($attachment_id, '_source_url', $image_url);

            return $attachment_id;

        } catch (\Exception $e) {
            $this->updateProgress('Erreur import image : ' . $e->getMessage());
            return false;
        }
    }

    private function mapDataToFields(array $data): array {
        $mapped_data = [
            'reference' => $data[1] ?? '', // Référence du bien
            'type_transaction' => $data[2] ?? '',
            'type_bien' => $data[3] ?? '',
            'code_postal' => $data[4] ?? '',
            'ville' => $data[5] ?? '',
            'prix' => $data[10] ?? '',
            'surface' => $data[15] ?? '',
            'pieces' => $data[17] ?? '',
            'chambres' => $data[18] ?? '',
            'titre' => $data[19] ?? '',
            'description' => $data[20] ?? '',
            'annee_construction' => $data[26] ?? '',
            'dpe' => $data[176] ?? '',
            'contact_tel' => $data[104] ?? '',
            'contact_email' => $data[106] ?? '',
            'images' => array_filter($data, function($value) {
                return preg_match('/^https?:\/\/.*\.(jpg|jpeg|png|gif)$/i', $value);
            })
        ];

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Données mappées : ' . print_r($mapped_data, true));
        }

        return $mapped_data;
    }

    private function sendProgressUpdate($message, $percentage) {
        $response = [
            'message' => is_string($message) ? $message : json_encode($message),
            'percentage' => $percentage
        ];

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Progress Update: ' . json_encode($response));
        }

        echo json_encode($response);
        flush();
        ob_flush();
    }

    private function handleError($error) {
        $error_message = is_wp_error($error) ? $error->get_error_message() : 
                       (is_object($error) ? json_encode($error) : (string)$error);
        
        $response = [
            'success' => false,
            'message' => "Erreur : " . $error_message
        ];

        if (DEBUG_UP_IMMO) {
            error_log('UP_IMMO - Error: ' . json_encode($response));
        }

        echo json_encode($response);
        die();
    }
} 