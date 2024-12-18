<?php
namespace UpImmo\Models;

use UpImmo\Models\Interfaces\BienInterface;

abstract class AbstractBien implements BienInterface {
    protected $post_id;
    protected $post;
    protected $meta_data;

    public function __construct(int $post_id) {
        $this->post_id = $post_id;
        $this->post = get_post($post_id);
        $this->meta_data = get_post_meta($post_id);
    }

    public function getId(): int {
        return $this->post_id;
    }

    public function getTitle(): string {
        return get_the_title($this->post_id);
    }

    abstract public function getPrice(): float;
    abstract public function getDescription(): string;
    abstract public function getImages(): array;
    abstract public function getMetaData(): array;
} 