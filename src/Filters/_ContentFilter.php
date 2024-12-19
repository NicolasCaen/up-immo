<?php
namespace UpImmo\Filters;

class ContentFilter extends AbstractFilter {
    protected function initializeFilters(): void {
        add_filter('up_immo_clean_import_data', [$this, 'apply'], 10, 2);
    }

    protected function removeFilters(): void {
        remove_filter('up_immo_clean_import_data', [$this, 'apply'], 10);
    }

    public function apply($value, string $field = ''): string {
        $value = $this->ensureString($value);
        if (empty($value)) {
            return '';
        }

        $value = $this->convertEncoding($value);

        switch ($field) {
            case 'description':
                // error_log('UP_IMMO -ContentFilter- Description : ' . $value);
                // $value = $this->normalizeAccents($value);
                // error_log('UP_IMMO -ContentFilter- Description 2 : ' . $value);
                // $value = $this->preserveLineBreaks($value);
                // error_log('UP_IMMO -ContentFilter-  Description 3 : ' . $value);
                // $value = $this->cleanSpecialChars($value);
                // error_log('UP_IMMO -ContentFilter- Description 4 : ' . $value);
                // $value = $this->cleanSpaces($value);
                // error_log('UP_IMMO -ContentFilter- Description 5 : ' . $value);
                break;

            case 'prix':
                $value = $this->formatPrice($value);
                break;

            default:
                $value = $this->removeInvisibleChars($value);
                $value = $this->cleanSpecialChars($value);
                $value = $this->cleanSpaces($value);
        }

        return $value;
    }

    public static function applyFilters($value, $field = '') {
        return apply_filters('up_immo_clean_import_data', $value, $field);
    }
} 