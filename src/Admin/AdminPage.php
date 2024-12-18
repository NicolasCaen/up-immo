<?php

namespace UpImmo\Admin;

class AdminPage {
    public function enqueueScripts() {
        wp_enqueue_script(
            'up-immo-admin',
            plugins_url('assets/js/admin.js', UP_IMMO_PLUGIN_FILE),
            ['jquery'],
            UP_IMMO_VERSION,
            true
        );

        wp_localize_script('up-immo-admin', 'upImmoAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('up_immo_admin')
        ]);
    }
} 