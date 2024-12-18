<?php

namespace UpImmo\Admin;

class AdminAjax {
    public function __construct() {
        add_action('wp_ajax_up_immo_get_progress', [$this, 'getProgress']);
    }

    public function getProgress() {
        check_ajax_referer('up_immo_admin', 'nonce');

        $progress = get_option('up_immo_import_progress', [
            'message' => 'En attente...',
            'percentage' => 0,
            'timestamp' => time()
        ]);
        
        if (!is_array($progress)) {
            $progress = [
                'message' => 'Erreur de format',
                'percentage' => 0,
                'timestamp' => time()
            ];
        }

        wp_send_json_success($progress);
    }
} 