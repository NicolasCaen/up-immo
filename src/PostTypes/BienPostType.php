<?php
namespace UpImmo\PostTypes;

class BienPostType {
    private static $hooks_registered = false;

    public function __construct() {
        add_action('init', [$this, 'register']);
        
        if (!self::$hooks_registered) {
            // Un seul hook pour gérer les colonnes
            add_filter('manage_bien_posts_columns', function($columns) {
                // Supprimer d'abord la colonne featured_image
                unset($columns['featured_image']);
                
                // Réorganiser les colonnes pour placer la miniature après la case à cocher
                $new_columns = [];
                foreach ($columns as $key => $value) {
                    if ($key === 'title') {
                        $new_columns['thumbnail'] = __('Miniature', 'up-immo');
                    }
                    $new_columns[$key] = $value;
                }
                
                return $new_columns;
            });
            
            add_action('manage_bien_posts_custom_column', [$this, 'displayThumbnailColumn'], 10, 2);
            add_action('before_delete_post', [$this, 'cleanupPostData']);
            
            self::$hooks_registered = true;
        }
    }

    public function register(): void {
        register_post_type('bien', [
            'labels' => [
                'name' => __('Biens', 'up-immo'),
                'singular_name' => __('Bien', 'up-immo'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions'
            ],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-building',
            'rewrite' => ['slug' => 'biens'],
            'register_meta_box_cb' => [$this, 'addMetaBoxes']
        ]);

        // Enregistrer automatiquement les meta fields basés sur le mapping
        $mapping = get_option('up_immo_mapping_json');
        if (!empty($mapping)) {
            $mapping = is_string($mapping) ? json_decode($mapping, true) : $mapping;
            
            // Configuration des types pour chaque champ connu
            $field_types = [
                'reference' => 'string',
                'titre' => 'string',
                'description' => 'string',
                'prix' => 'number',
                'surface' => 'number',
                'pieces' => 'integer',
                'chambres' => 'integer',
                'code_postal' => 'string',
                'ville' => 'string',
                'dpe' => 'string',
                'contact_tel' => 'string',
                'contact_email' => 'string'
            ];

            foreach ($mapping as $field => $index) {
                $type = $field_types[$field] ?? 'string'; // Par défaut 'string' si non défini
                
                register_post_meta('bien', $field, [
                    'type' => $type,
                    'single' => true,
                    'show_in_rest' => true,
                    'sanitize_callback' => function($meta_value) use ($type) {
                        switch ($type) {
                            case 'number':
                                return is_numeric($meta_value) ? floatval($meta_value) : 0;
                            case 'integer':
                                return is_numeric($meta_value) ? intval($meta_value) : 0;
                            case 'string':
                            default:
                                return sanitize_text_field($meta_value);
                        }
                    }
                ]);
            }
        }
    }

    public function addMetaBoxes($post): void {
        add_meta_box(
            'bien_details',
            __('Détails du bien', 'up-immo'),
            [$this, 'renderMetaBox'],
            'bien',
            'normal',
            'high'
        );
    }

    protected function getMetaFields(): array {
        $mapping = get_option('up_immo_mapping_json');
        if (empty($mapping)) {
            return [];
        }

        $mapping = is_string($mapping) ? json_decode($mapping, true) : $mapping;
        
        // Configuration par défaut pour les champs connus
        $field_configs = [
            'reference' => [
                'label' => __('Référence:', 'up-immo'),
                'type' => 'text',
            ],
            'titre' => [
                'label' => __('Titre:', 'up-immo'),
                'type' => 'text',
            ],
            'description' => [
                'label' => __('Description:', 'up-immo'),
                'type' => 'textarea',
            ],
            'prix' => [
                'label' => __('Prix:', 'up-immo'),
                'type' => 'number',
            ],
            'surface' => [
                'label' => __('Surface:', 'up-immo'),
                'type' => 'number',
                'step' => '0.01',
            ],
            'pieces' => [
                'label' => __('Pièces:', 'up-immo'),
                'type' => 'number',
            ],
            'chambres' => [
                'label' => __('Chambres:', 'up-immo'),
                'type' => 'number',
            ],
            'code_postal' => [
                'label' => __('Code postal:', 'up-immo'),
                'type' => 'text',
                'pattern' => '[0-9]{5}',
            ],
            'ville' => [
                'label' => __('Ville:', 'up-immo'),
                'type' => 'text',
            ],
            'dpe' => [
                'label' => __('DPE:', 'up-immo'),
                'type' => 'select',
                'options' => ['A', 'B', 'C', 'D', 'E', 'F', 'G'],
            ],
            'contact_tel' => [
                'label' => __('Téléphone:', 'up-immo'),
                'type' => 'tel',
            ],
            'contact_email' => [
                'label' => __('Email:', 'up-immo'),
                'type' => 'email',
            ],
            'attached_images' => [
                'label' => __('IDs des images:', 'up-immo'),
                'type' => 'readonly',
                'description' => __('IDs des images attachées à ce bien', 'up-immo'),
            ],
        ];

        // Traiter tous les champs du mapping
        $fields = [];
        foreach ($mapping as $field => $index) {
            if (isset($field_configs[$field])) {
                $fields[$field] = $field_configs[$field];
            } else {
                // Configuration par défaut pour les champs non définis
                $fields[$field] = [
                    'label' => ucfirst(str_replace('_', ' ', $field)) . ':',
                    'type' => 'text'
                ];
            }
        }

        return $fields;
    }

    public function renderMetaBox($post): void {
        wp_nonce_field('bien_meta_box', 'bien_meta_box_nonce');
        
        $fields = $this->getMetaFields();
        if (empty($fields)) {
            echo '<p>' . __('Veuillez configurer le mapping des champs dans les réglages.', 'up-immo') . '</p>';
            return;
        }

        echo '<div class="bien-meta-box__container"><div class="bien-meta-box__items">';
        foreach ($fields as $key => $field) {
            if ($key === 'attached_images') {
                // Récupérer toutes les images attachées
                $attachments = get_attached_media('image', $post->ID);
                $image_ids = [];
                foreach ($attachments as $attachment) {
                    $image_ids[] = $attachment->ID;
                }
                $value = implode(', ', $image_ids);
                
                echo '<div class="bien-meta-box__item bien-meta-box__item--' . esc_attr($key) . '">';
                echo '<label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
                echo '<input type="text" id="' . esc_attr($key) . '" value="' . esc_attr($value) . '" readonly>';
                if (!empty($field['description'])) {
                    echo '<p class="description">' . esc_html($field['description']) . '</p>';
                }
                echo '</div>';
                continue;
            }

            $value = get_post_meta($post->ID, $key, true);
            
            echo '<div class="bien-meta-box__item bien-meta-box__item--' . esc_attr($key) . '">';
            echo '<label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
            
            switch ($field['type']) {
                case 'textarea':
                    echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">' . 
                         esc_textarea($value) . '</textarea>';
                    break;
                    
                case 'select':
                    echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
                    foreach ($field['options'] as $option) {
                        echo '<option value="' . esc_attr($option) . '"' . 
                             selected($value, $option, false) . '>' . 
                             esc_html($option) . '</option>';
                    }
                    echo '</select>';
                    break;
                    
                default:
                    echo '<input type="' . esc_attr($field['type']) . '" ' .
                         'id="' . esc_attr($key) . '" ' .
                         'name="' . esc_attr($key) . '" ' .
                         'value="' . esc_attr($value) . '"';
                    
                    if (isset($field['pattern'])) {
                        echo ' pattern="' . esc_attr($field['pattern']) . '"';
                    }
                    if (isset($field['step'])) {
                        echo ' step="' . esc_attr($field['step']) . '"';
                    }
                    
                    echo '>';
            }
            echo '</div>';
        }
        echo '</div></div>';
    }

    public function displayThumbnailColumn($column, $post_id): void {
        if ($column === 'thumbnail') {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) {
                echo wp_get_attachment_image($thumbnail_id, [100, 100], false);
            } else { 
                echo '<img src="' . plugins_url('assets/images/no-image.png', dirname(dirname(__FILE__))) . '" width="50" height="50" alt="Pas d\'image" />';
            }
        }
    }

    public function cleanupPostData(int $post_id): void {
        // Vérifier si c'est bien un 'bien' qu'on supprime
        if (get_post_type($post_id) !== 'bien') {
            return;
        }

        // Supprimer toutes les meta-données
        $meta_keys = get_post_custom_keys($post_id);
        if (!empty($meta_keys)) {
            foreach ($meta_keys as $key) {
                delete_post_meta($post_id, $key);
            }
        }

        // Récupérer et supprimer les images attachées
        $attachments = get_attached_media('', $post_id);
        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true); // true = supprimer aussi le fichier
        }
    }
} 