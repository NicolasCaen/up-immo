<?php
namespace UpImmo\Filters;

class PriceFilter extends AbstractFilter {
    protected function initializeFilters(): void {
        add_filter('up_immo_format_price', [$this, 'apply'], 10, 1);
    }

    protected function removeFilters(): void {
        remove_filter('up_immo_format_price', [$this, 'apply'], 10);
    }

    public function apply($value, string $field = ''): string {
        return $this->formatPrice($this->ensureString($value));
    }

    public static function applyFilters($value) {
        return apply_filters('up_immo_format_price', $value);
    }
} 