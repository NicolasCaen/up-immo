<?php
namespace UpImmo\Filters;

abstract class AbstractFilter {
    protected $encoding;

    public function __construct(string $encoding = 'ISO-8859-1') {
        $this->encoding = $encoding;
        $this->initializeFilters();
    }

    abstract protected function initializeFilters(): void;
    abstract protected function removeFilters(): void;

    // Méthodes utilitaires communes
    protected function convertEncoding($value): string {
        if (empty($value)) return '';
        
        if ($this->encoding !== 'UTF-8') {
            return iconv($this->encoding, 'UTF-8//TRANSLIT//IGNORE', $value);
        }
        return $value;
    }

    protected function cleanSpecialChars($value): string {
        return preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $value);
    }

    protected function removeInvisibleChars($value): string {
        return str_replace(['�', '?'], '', $value);
    }

    protected function cleanSpaces($value): string {
        return preg_replace('/\s+/', ' ', trim($value));
    }

    protected function ensureString($value): string {
        if ($value === null || $value === false) {
            return '';
        }
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return (string)$value;
    }

    protected function cleanHtml($value): string {
        // Nettoyer le HTML tout en préservant certaines balises
        return wp_kses($value, [
            'p' => [],
            'br' => [],
            'strong' => [],
            'em' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
        ]);
    }

    protected function formatPrice($value): string {
        // Nettoyer et formater les prix
        $value = preg_replace('/[^0-9.]/', '', $value);
        return number_format((float)$value, 2, '.', '');
    }

    protected function normalizeAccents($value): string {
        return str_replace(
            ['', 'é', 'è', 'à', 'ê', 'â', 'î', 'ô', 'û', 'ë', 'ï', 'ü'], 
            ['e', 'e', 'e', 'a', 'e', 'a', 'i', 'o', 'u', 'e', 'i', 'u'], 
            $value
        );
    }

    protected function preserveLineBreaks($value): string {
        return str_replace(["\r\n", "\r", "\n"], '<br>', $value);
    }
} 