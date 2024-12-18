<?php
namespace UpImmo\Models;

class Bien extends AbstractBien {
    public function getPrice(): float {
        return (float) get_post_meta($this->post_id, '_price', true);
    }

    public function getDescription(): string {
        return get_post_field('post_content', $this->post_id);
    }

    public function getImages(): array {
        $images = [];
        if (has_post_thumbnail($this->post_id)) {
            $images[] = get_post_thumbnail_id($this->post_id);
        }
        return $images;
    }

    public function getMetaData(): array {
        return array_map(function($meta) {
            return $meta[0];
        }, $this->meta_data);
    }
} 