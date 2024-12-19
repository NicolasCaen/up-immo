<?php
namespace UpImmo\Filters;

class ContentFilters {
    protected $encoding;

    public function __construct(string $encoding = 'ISO-8859-1') {
        $this->encoding = $encoding;
        $this->initializeFilters();
    }

    public function initializeFilters(): void {
        add_filter('up_immo_clean_import_data', [$this, 'defaultFilter'], 5);
        add_filter('up_immo_clean_import_data', [$this, 'cleanData'], 10);
        add_filter('up_immo_clean_import_data', [$this, 'handleEncoding'], 15);
    }

    public function removeFilters(): void {
        remove_filter('up_immo_clean_import_data', [$this, 'defaultFilter'], 5);
        remove_filter('up_immo_clean_import_data', [$this, 'cleanData'], 10);
        remove_filter('up_immo_clean_import_data', [$this, 'handleEncoding'], 15);
    }

    /**
     * Filtre par défaut qui convertit en chaîne
     */
    public function defaultFilter($value) {
        return $this->ensureString($value);
    }

    /**
     * Nettoie les données
     */
    public function cleanData($value) {
        $value = $this->ensureString($value);
        
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value);
        if ($value === null) {
            return '';
        }

        $value = preg_replace('/\s+/', ' ', $value);
        if ($value === null) {
            return '';
        }

        return trim($value);
    }

    /**
     * Gère l'encodage des données
     */
    public function handleEncoding($value) {
        $value = $this->ensureString($value);
        
        if ($value === '') {
            return '';
        }

        if ($this->encoding !== 'UTF-8') {
            $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
            
            if (strpos($value, '�') !== false) {
                $detected_encoding = mb_detect_encoding($value, ['ISO-8859-1', 'ISO-8859-15', 'UTF-8', 'ASCII']);
                $value = mb_convert_encoding($value, 'UTF-8', $detected_encoding ?: 'ISO-8859-1');
            }
        }
        
        return $value;
    }

    /**
     * Assure qu'une valeur est une chaîne
     */
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
    public static function applyFilters($value) {
        return apply_filters('up_immo_clean_import_data', $value);
    }
} 