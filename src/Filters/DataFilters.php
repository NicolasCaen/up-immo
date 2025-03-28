<?php

 namespace UpImmo\Filters;
 class DataFilters {
     private static $filters = [];
     public static function registerFilter(string $field, callable $callback): void {
         if (!isset(self::$filters[$field])) {
             self::$filters[$field] = [];
         }
         self::$filters[$field][] = $callback;
     }
     public static function applyFilters(string $field, $value) {
         if (!isset(self::$filters[$field])) {
             return $value;
         }
         foreach (self::$filters[$field] as $filter) {
             $value = $filter($value);
         }
         return $value;
     }
     public static function formatPrice($value): string {
         return number_format((float)$value, 0, ',', ' ') . ' €';
     }
     public static function formatSurface($value): string {
         return number_format((float)$value, 0, ',', ' ') . ' m²';
     }
     public static function formatPieces($value): string {
         return $value . ' pièce' . ($value > 1 ? 's' : '');
     }
     public static function formatChambres($value): string {
         return $value . ' chambre' . ($value > 1 ? 's' : '');
     }
 } 