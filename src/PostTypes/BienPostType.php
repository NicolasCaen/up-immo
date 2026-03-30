<?php
namespace UpImmo\PostTypes;

class BienPostType {
    public function __construct() {
        add_action('init', [$this, 'register']);
        add_action('save_post', [$this, 'saveMetaBox'], 10, 2);
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
        register_post_meta('bien', 'ville', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'description', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'type', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'annee_construction', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'surface_terrain', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'type_chauffage', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'diag', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'energie', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'energie_lettre', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'ges', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'ges_lettre', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
        register_post_meta('bien', 'dpe_date', [
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
        $ville = get_post_meta($post->ID, 'ville', true);
        $description = get_post_meta($post->ID, 'description', true);
        $type = get_post_meta($post->ID, 'type', true);
        $annee_construction = get_post_meta($post->ID, 'annee_construction', true);
        $surface_terrain = get_post_meta($post->ID, 'surface_terrain', true);
        $type_chauffage = get_post_meta($post->ID, 'type_chauffage', true);
        $diag = get_post_meta($post->ID, 'diag', true);
        $energie = get_post_meta($post->ID, 'energie', true);
        $energie_lettre = get_post_meta($post->ID, 'energie_lettre', true);
        $ges = get_post_meta($post->ID, 'ges', true);
        $ges_lettre = get_post_meta($post->ID, 'ges_lettre', true);
        $dpe_date = get_post_meta($post->ID, 'dpe_date', true);

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
            <p>
                <label for="ville"><?php _e('Ville:', 'up-immo'); ?></label>
                <input type="text" id="ville" name="ville" value="<?php echo esc_attr($ville); ?>">
            </p>
            <p>
                <label for="type"><?php _e('Type de bien:', 'up-immo'); ?></label>
                <input type="text" id="type" name="type" value="<?php echo esc_attr($type); ?>">
            </p>
            <p>
                <label for="annee_construction"><?php _e('Année de construction:', 'up-immo'); ?></label>
                <input type="text" id="annee_construction" name="annee_construction" value="<?php echo esc_attr($annee_construction); ?>">
            </p>
            <p>
                <label for="surface_terrain"><?php _e('Surface terrain:', 'up-immo'); ?></label>
                <input type="text" id="surface_terrain" name="surface_terrain" value="<?php echo esc_attr($surface_terrain); ?>">
            </p>
            <p>
                <label for="type_chauffage"><?php _e('Type de chauffage:', 'up-immo'); ?></label>
                <input type="text" id="type_chauffage" name="type_chauffage" value="<?php echo esc_attr($type_chauffage); ?>">
            </p>
            <p>
                <label for="diag"><?php _e('Diagnostic:', 'up-immo'); ?></label>
                <input type="text" id="diag" name="diag" value="<?php echo esc_attr($diag); ?>">
            </p>
            <p>
                <label for="energie"><?php _e('Énergie (kWh/m²/an):', 'up-immo'); ?></label>
                <input type="text" id="energie" name="energie" value="<?php echo esc_attr($energie); ?>">
            </p>
            <p>
                <label for="energie_lettre"><?php _e('Lettre énergie (A-G):', 'up-immo'); ?></label>
                <input type="text" id="energie_lettre" name="energie_lettre" value="<?php echo esc_attr($energie_lettre); ?>">
            </p>
            <p>
                <label for="ges"><?php _e('GES (kgCO2/m²/an):', 'up-immo'); ?></label>
                <input type="text" id="ges" name="ges" value="<?php echo esc_attr($ges); ?>">
            </p>
            <p>
                <label for="ges_lettre"><?php _e('Lettre GES (A-G):', 'up-immo'); ?></label>
                <input type="text" id="ges_lettre" name="ges_lettre" value="<?php echo esc_attr($ges_lettre); ?>">
            </p>
            <p>
                <label for="dpe_date"><?php _e('Date DPE:', 'up-immo'); ?></label>
                <input type="text" id="dpe_date" name="dpe_date" value="<?php echo esc_attr($dpe_date); ?>">
            </p>
            <p>
                <label for="description"><?php _e('Description:', 'up-immo'); ?></label>
                <textarea id="description" name="description" rows="5" cols="50"><?php echo esc_textarea($description); ?></textarea>
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

    public function saveMetaBox($post_id, $post): void {
        if (!isset($_POST['bien_meta_box_nonce']) || !wp_verify_nonce($_POST['bien_meta_box_nonce'], 'bien_meta_box')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $fields = [
            'reference', 'prix', 'surface', 'pieces', 'chambres',
            'code_postal', 'dpe', 'contact_tel', 'contact_email',
            'ville', 'description', 'type', 'annee_construction',
            'surface_terrain', 'type_chauffage', 'diag',
            'energie', 'energie_lettre', 'ges', 'ges_lettre', 'dpe_date'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
} 