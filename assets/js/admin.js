(function($) {
    'use strict';

    const importForm = $('#upImmoImportForm');
    const progressContainer = $('.up-immo-import-progress');
    const progressBar = $('.progress-bar__fill');
    const progressText = $('.progress-text');

    importForm.on('submit', function(e) {
        e.preventDefault();
        
        const strategy = $('#import_strategy').val();
        if (!strategy) {
            alert(up_immo_vars.messages.no_strategy);
            return;
        }

        // Récupérer le nonce du formulaire directement
        const nonce = $('input[name="nonce"]').val();

        // Afficher la barre de progression
        progressContainer.show();
        progressBar.css('width', '0%');
        progressText.text(up_immo_vars.messages.starting);

        // Désactiver le bouton pendant l'import
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);

        // Envoyer la requête AJAX
        $.ajax({
            url: up_immo_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'up_immo_import',
                nonce: nonce,
                import_strategy: strategy
            },
            success: function(response) {
                if (response.success) {
                    progressBar.css('width', '100%');
                    progressText.text(response.data.message);
                } else {
                    progressText.text(response.data.message || up_immo_vars.messages.error);
                    console.error('Erreur:', response.data);
                }
            },
            error: function(xhr, status, error) {
                progressText.text(up_immo_vars.messages.error);
                console.error('Erreur AJAX:', status, error);
            },
            complete: function() {
                submitButton.prop('disabled', false);
            }
        });
    });

    // Fonction pour mettre à jour la progression
    function checkProgress() {
        if (!progressContainer.is(':visible')) return;

        const nonce = $('input[name="nonce"]').val();

        $.ajax({
            url: up_immo_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'up_immo_get_progress',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    const progress = response.data;
                    progressBar.css('width', progress.percentage + '%');
                    progressText.text(progress.message);
                }
            }
        });
    }

    // Vérifier la progression toutes les 2 secondes
    setInterval(checkProgress, 2000);
})(jQuery); 