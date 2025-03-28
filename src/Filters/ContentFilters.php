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
        
        // Ajouter les filtres de formatage
        add_filter('up_immo_format_prix', [$this, 'formatPrice'], 10, 1);
        add_filter('up_immo_format_surface', [$this, 'formatSurface'], 10, 1);
        add_filter('up_immo_format_pieces', [$this, 'formatPieces'], 10, 1);
        add_filter('up_immo_format_chambres', [$this, 'formatChambres'], 10, 1);
    }

    public function removeFilters(): void {
        remove_filter('up_immo_clean_import_data', [$this, 'defaultFilter'], 5);
        remove_filter('up_immo_clean_import_data', [$this, 'cleanData'], 10);
        remove_filter('up_immo_clean_import_data', [$this, 'handleEncoding'], 15);
        
        // Retirer les filtres de formatage
        remove_filter('up_immo_format_prix', [$this, 'formatPrice'], 10);
        remove_filter('up_immo_format_surface', [$this, 'formatSurface'], 10);
        remove_filter('up_immo_format_pieces', [$this, 'formatPieces'], 10);
        remove_filter('up_immo_format_chambres', [$this, 'formatChambres'], 10);
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

        // Appliquer la correction des caractères spéciaux pour tous les champs textuels
        if ($field !== 'prix' && $field !== 'surface' && $field !== 'pieces' && $field !== 'chambres') {
            $value = $this->fixSpecialCharacters($value);
        }
        
        // Traitement spécifique selon le champ
        switch ($field) {
            case 'titre':
            case 'title':
                // Nettoyer les caractères invisibles et espaces multiples
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value) ?? $value;
                $value = preg_replace('/\s+/', ' ', $value) ?? $value;
                break;
                
            case 'description':
                // Préserver les retours à la ligne
                $value = str_replace(["\r\n", "\r", "\n"], '<br>', $value);
                // Nettoyer les caractères invisibles
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value) ?? $value;
                // Nettoyer les espaces multiples
                $value = preg_replace('/\s+/', ' ', $value) ?? $value;
                break;

            case 'prix':
                // Nettoyer les caractères non numériques
                $value = preg_replace('/[^0-9.]/', '', $value);
                break;

            default:
                // Nettoyage standard
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value) ?? $value;
                $value = preg_replace('/\s+/', ' ', $value) ?? $value;
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
        
        // Appliquer directement la correction des caractères spéciaux
        // Cette méthode est plus efficace que de tenter de détecter l'encodage
        return $this->fixSpecialCharacters($value);
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
     * Corrige les caractères spéciaux problématiques
     */
    private function fixSpecialCharacters(string $value): string {
        // Remplacer directement les points d'interrogation qui remplacent les accents
        $value = str_replace('?', 'à', $value);
        
        // Approche directe : utiliser utf8_decode pour les caractères mal encodés
        $decoded = utf8_decode($value);
        
        // Si le décodage a fonctionné, le convertir en UTF-8
        if (mb_check_encoding($decoded, 'ISO-8859-1')) {
            return mb_convert_encoding($decoded, 'UTF-8', 'ISO-8859-1');
        }
        
        // Si le décodage simple ne fonctionne pas, utiliser une table de correspondance
        $replacements = [
            // Corrections courantes pour le français
            'Ã©' => 'é',
            'Ã¨' => 'è',
            'Ãª' => 'ê',
            'Ã«' => 'ë',
            'Ã ' => 'à',
            'Ã¢' => 'â',
            'Ã®' => 'î',
            'Ã¯' => 'ï',
            'Ã´' => 'ô',
            'Ã¶' => 'ö',
            'Ã¹' => 'ù',
            'Ã»' => 'û',
            'Ã¼' => 'ü',
            'Ã§' => 'ç',
            'Ã´' => 'ô',
            'Ã¸' => 'ø',
            // Remplacements pour les points d'interrogation
            '?' => 'à',
            'à ?' => 'à à',
            'à?' => 'à à'
        ];
        
        // Appliquer les remplacements
        $value = str_replace(array_keys($replacements), array_values($replacements), $value);
        
        // Nettoyer les espaces multiples qui pourraient être créés
        return preg_replace('/\s+/', ' ', $value);
    }

    /**
     * Méthode utilitaire pour appliquer tous les filtres
     */
    public static function applyFilters($value, $field = '') {
        $value = apply_filters('up_immo_clean_import_data', $value, $field);
        
        // Appliquer les filtres de formatage selon le champ
        switch ($field) {
            case 'prix':
                return apply_filters('up_immo_format_prix', $value);
            case 'surface':
                return apply_filters('up_immo_format_surface', $value);
            case 'pieces':
                return apply_filters('up_immo_format_pieces', $value);
            case 'chambres':
                return apply_filters('up_immo_format_chambres', $value);
            default:
                return $value;
        }
    }

    // Méthodes de formatage
    public function formatPrice($value): string {
        return number_format((float)$value, 0, ',', ' ') . ' €';
    }

    public function formatSurface($value): string {
        return number_format((float)$value, 0, ',', ' ') . ' m²';
    }

    public function formatPieces($value): string {
        return $value . ' pièce' . ($value > 1 ? 's' : '');
    }

    public function formatChambres($value): string {
        return $value . ' chambre' . ($value > 1 ? 's' : '');
    }
} 