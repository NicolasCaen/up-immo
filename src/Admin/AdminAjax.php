<?php

namespace UpImmo\Admin;

/**
 * Handle Admin AJAX requests
 */
class AdminAjax {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_up_immo_get_progress', [$this, 'getProgress']);
    }

    /**
     * Get import progress
     */
    public function getProgress() {
        // Check AJAX nonce
        check_ajax_referer('up_immo_admin', 'nonce');

        // Get import progress from options
        $progress = get_option('up_immo_import_progress', [
            'message' => 'En attente...',
            'percentage' => 0,
            'timestamp' => time()
        ]);

        // Ensure progress is an array
        if (!is_array($progress)) {
            $progress = [
                'message' => 'Erreur de format',
                'percentage' => 0,
                'timestamp' => time()
            ];
        }

        // Send progress as JSON response
        wp_send_json_success($progress);
    }
}