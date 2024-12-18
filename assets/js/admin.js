alert('ok');
(function($) {
    'use strict';

    const UpImmoImport = {
        init: function() {
            console.log('Initialisation...');
            this.form = $('#upImmoImportForm');
            console.log('Formulaire trouvé:', this.form.length > 0);
            
            if (!this.form.length) return;

            this.progress = $('.up-immo-import-progress');
            this.progressBar = $('.progress-bar__fill');
            this.progressText = $('.progress-text');
            this.submitButton = this.form.find('button[type="submit"]');
            
            console.log('Éléments trouvés:', {
                progress: this.progress.length > 0,
                progressBar: this.progressBar.length > 0,
                progressText: this.progressText.length > 0,
                submitButton: this.submitButton.length > 0
            });
            
            this.bindEvents();
        },

        bindEvents: function() {
            console.log('Binding des événements...');
            this.form.on('submit', (e) => {
                console.log('Formulaire soumis');
                this.handleSubmit(e);
            });
        },

        handleSubmit: function(e) {
            e.preventDefault();
            console.log('Gestion de la soumission...');

            const filePath = this.form.find('#file_path').val();
            const strategy = this.form.find('#import_strategy').val();
            
            console.log('Valeurs du formulaire:', {
                filePath: filePath,
                strategy: strategy
            });

            if (!filePath || !strategy) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            const formData = new FormData(this.form[0]);
            formData.append('action', 'up_immo_import');
            
            console.log('FormData créé');

            this.submitButton.prop('disabled', true);
            this.progress.show();
            this.progressBar.css('width', '0%');
            this.progressText.text('Démarrage de l\'import...');

            this.startImport(formData);
            this.startProgressTracking();
        },

        startImport: function(formData) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleSuccess.bind(this),
                error: this.handleError.bind(this)
            });
        },

        startProgressTracking: function() {
            this.checkProgress();
        },

        checkProgress: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'up_immo_get_progress',
                    nonce: $('#upImmoImportForm [name="nonce"]').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.updateProgress(response.data.percentage);
                        this.progressText.text(response.data.message);
                        
                        if (response.data.percentage < 100) {
                            setTimeout(() => this.checkProgress(), 500);
                        } else {
                            this.submitButton.prop('disabled', false);
                            // Optionnel : message de succès
                            setTimeout(() => {
                                alert('Import terminé avec succès !');
                            }, 500);
                        }
                    }
                },
                error: this.handleError.bind(this)
            });
        },

        handleSuccess: function(response) {
            if (!response.success) {
                this.handleError(response.data.message || 'Une erreur est survenue');
            }
        },

        handleError: function(error) {
            console.error('Erreur:', error);
            this.progressText.html(`<span style="color: red;">Erreur : ${error}</span>`);
            this.submitButton.prop('disabled', false);
        },

        updateProgress: function(percentage) {
            this.progressBar.css('width', percentage + '%');
        }
    };

    // S'assurer que le document est prêt
    $(function() {
        console.log('Document prêt, initialisation de UpImmoImport');
        UpImmoImport.init();
    });

})(jQuery); 