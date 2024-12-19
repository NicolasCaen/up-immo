<?php
namespace UpImmo\Filters;

/**
 * La classe ContentFilters est utilisée pour gérer et appliquer des filtres
 * sur les données importées dans le plugin UpImmo. En particulier, elle se concentre
 * sur le nettoyage et la normalisation des données importées via le filtre 'up_immo_clean_import_data'.
 * 
 * Les filtres appliqués par cette classe incluent :
 * - defaultFilter : Convertit les valeurs en chaînes de caractères.
 * - cleanData : Nettoie les données en supprimant les caractères spéciaux et en préservant les retours à la ligne.
 * - handleEncoding : Gère l'encodage des données pour s'assurer qu'elles sont en UTF-8.
 * 
 * Ces filtres sont ajoutés et supprimés dynamiquement lors de l'initialisation et de la suppression des filtres.
 */

class ContentFilters {
    protected $encoding;

    public function __construct(string $encoding = 'ISO-8859-1') {
        $this->encoding = $encoding;
        $this->initializeFilters();
    }

    public function initializeFilters(): void {
        add_filter('up_immo_clean_import_data', [$this, 'defaultFilter'], 5, 2);
        add_filter('up_immo_clean_import_data', [$this, 'cleanData'], 10, 2);
        add_filter('up_immo_clean_import_data', [$this, 'handleEncoding'], 15, 2);
    }

    public function removeFilters(): void {
        remove_filter('up_immo_clean_import_data', [$this, 'defaultFilter'], 5);
        remove_filter('up_immo_clean_import_data', [$this, 'cleanData'], 10);
        remove_filter('up_immo_clean_import_data', [$this, 'handleEncoding'], 15);
    }

    /**
     * Filtre par défaut qui convertit en chaîne
     */
    public function defaultFilter($value, $field = '') {
        return $this->ensureString($value);
    }

    /**
     * Nettoie les données
     */
    public function cleanData($value, $field = '') {
        $value = $this->ensureString($value);
        
        if ($value === '') {
            return '';
        }

        // Traitement spécifique selon le champ
        switch ($field) {
            case 'description':
                error_log('UP_IMMO - Description : ' . $value);
                // Nettoyer les caractères spéciaux problématiques
                $value = str_replace(['', 'é', 'è', 'à', 'ê', 'â', 'î', 'ô', 'û', 'ë', 'ï', 'ü'], 
                                   ['e', 'e', 'e', 'a', 'e', 'a', 'i', 'o', 'u', 'e', 'i', 'u'], 
                                   $value);
                                   error_log('UP_IMMO - Description 2 : ' . $value);
                // Préserver les retours à la ligne
                $value = str_replace(["\r\n", "\r", "\n"], '<br>', $value);
                error_log('UP_IMMO - Description 3 : ' . $value);
                // Nettoyer les autres caractères invisibles
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value);
                if ($value === null) return '';
                error_log('UP_IMMO - Description 4 : ' . $value);
                // Nettoyer les espaces multiples
                $value = preg_replace('/\s+/', ' ', $value);
                if ($value === null) return '';
                error_log('UP_IMMO - Description 5 : ' . $value);
                break;

            case 'prix':
                // Nettoyer les caractères non numériques
                $value = preg_replace('/[^0-9.]/', '', $value);
                break;

            default:
                // Nettoyage standard
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value);
                if ($value === null) return '';
                
                $value = preg_replace('/\s+/', ' ', $value);
                if ($value === null) return '';
        }

        return trim($value);
    }

    /**
     * Gère l'encodage des données
     */
    public function handleEncoding($value, $field = '') {
        $value = $this->ensureString($value);
        
        if ($value === '') {
            return '';
        }

        if ($this->encoding !== 'UTF-8') {
            // Traitement spécifique selon le champ si nécessaire
            switch ($field) {
                case 'description':
                    // Traitement spécial pour la description si nécessaire
                    break;
                default:
                    $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
                    
                    if (strpos($value, '') !== false) {
                        $detected_encoding = mb_detect_encoding($value, ['ISO-8859-1', 'ISO-8859-15', 'UTF-8', 'ASCII']);
                        $value = mb_convert_encoding($value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
                    }
            }
        }
        
        return $value;
    }

    private function ensureString($value): string {
        if ($value === null || $value === false) {
            return '';
        }
        
        if (is_array($value) || is_object($value)) {
            return '';
        }
        
        return (string)$value;
    }

    /**
     * Méthode utilitaire pour appliquer tous les filtres
     */
    public static function applyFilters($value, $field = '') {
        return apply_filters('up_immo_clean_import_data', $value, $field);
    }
} 