<?php
namespace UpImmo\PostTypes;

class BienPostType {
    public function __construct() {
        add_action('init', [$this, 'register']);
        add_filter('manage_bien_posts_columns', [$this, 'addThumbnailColumn']);
        add_action('manage_bien_posts_custom_column', [$this, 'displayThumbnailColumn'], 10, 2);
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

        // Enregistrer les meta fields
        register_post_meta('bien', 'reference', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'prix', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'surface', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'pieces', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'chambres', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'code_postal', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'dpe', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'contact_tel', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'contact_email', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
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

    public function renderMetaBox($post): void {
        // Récupérer les valeurs existantes
        $reference = get_post_meta($post->ID, 'reference', true);
        $prix = get_post_meta($post->ID, 'prix', true);
        $surface = get_post_meta($post->ID, 'surface', true);
        $pieces = get_post_meta($post->ID, 'pieces', true);
        $chambres = get_post_meta($post->ID, 'chambres', true);
        $code_postal = get_post_meta($post->ID, 'code_postal', true);
        $dpe = get_post_meta($post->ID, 'dpe', true);
        $contact_tel = get_post_meta($post->ID, 'contact_tel', true);
        $contact_email = get_post_meta($post->ID, 'contact_email', true);

        // Ajouter un nonce pour la sécurité
        wp_nonce_field('bien_meta_box', 'bien_meta_box_nonce');

        // Afficher les champs
        ?>
        <div class="bien-meta-box">
            <p>
                <label for="reference"><?php _e('Référence:', 'up-immo'); ?></label>
                <input type="text" id="reference" name="reference" value="<?php echo esc_attr($reference); ?>">
            </p>
            <p>
                <label for="prix"><?php _e('Prix:', 'up-immo'); ?></label>
                <input type="text" id="prix" name="prix" value="<?php echo esc_attr($prix); ?>">
            </p>
            <p>
                <label for="surface"><?php _e('Surface:', 'up-immo'); ?></label>
                <input type="text" id="surface" name="surface" value="<?php echo esc_attr($surface); ?>">
            </p>
            <p>
                <label for="pieces"><?php _e('Pièces:', 'up-immo'); ?></label>
                <input type="text" id="pieces" name="pieces" value="<?php echo esc_attr($pieces); ?>">
            </p>
            <p>
                <label for="chambres"><?php _e('Chambres:', 'up-immo'); ?></label>
                <input type="text" id="chambres" name="chambres" value="<?php echo esc_attr($chambres); ?>">
            </p>
            <p>
                <label for="code_postal"><?php _e('Code postal:', 'up-immo'); ?></label>
                <input type="text" id="code_postal" name="code_postal" value="<?php echo esc_attr($code_postal); ?>">
            </p>
            <p>
                <label for="dpe"><?php _e('DPE:', 'up-immo'); ?></label>
                <input type="text" id="dpe" name="dpe" value="<?php echo esc_attr($dpe); ?>">
            </p>
            <p>
                <label for="contact_tel"><?php _e('Téléphone:', 'up-immo'); ?></label>
                <input type="text" id="contact_tel" name="contact_tel" value="<?php echo esc_attr($contact_tel); ?>">
            </p>
            <p>
                <label for="contact_email"><?php _e('Email:', 'up-immo'); ?></label>
                <input type="email" id="contact_email" name="contact_email" value="<?php echo esc_attr($contact_email); ?>">
            </p>
        </div>
        <?php
    }

    public function addThumbnailColumn($columns): array {
        $columns['thumbnail'] = __('Miniature', 'up-immo');
        return $columns;
    }

    public function displayThumbnailColumn($column, $post_id): void {
        if ($column === 'thumbnail') {
            echo get_the_post_thumbnail($post_id, [50, 50]);
        }
    }
} 