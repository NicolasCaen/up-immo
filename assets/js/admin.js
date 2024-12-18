(function($) {
    'use strict';

    const UpImmoImport = {
        init: function() {
            this.form = $('#upImmoImportForm');
            this.progress = $('.up-immo-import-progress');
            this.progressBar = $('.progress-bar__fill');
            this.progressText = $('.progress-text');

            this.bindEvents();
        },

        bindEvents: function() {
            this.form.on('submit', this.handleSubmit.bind(this));
        },

        handleSubmit: function(e) {
            e.preventDefault();

            const formData = new FormData(this.form[0]);
            formData.append('action', 'up_immo_import');
            formData.append('security', $('#up_immo_nonce').val());

            this.form.find('button').prop('disabled', true);
            this.progress.show();

            this.startImport(formData);
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

        handleSuccess: function(response) {
            if (response.success) {
                const progress = response.data.progress;
                this.updateProgress(progress.percentage);
                this.progressText.text(response.data.message);

                if (progress.percentage < 100) {
                    // Continue import if not finished
                    setTimeout(() => {
                        this.startImport(new FormData(this.form[0]));
                    }, 1000);
                } else {
                    this.form.find('button').prop('disabled', false);
                }
            } else {
                this.handleError(response.data.message);
            }
        },

        handleError: function(error) {
            this.progressText.html(`<span class="error">${error}</span>`);
            this.form.find('button').prop('disabled', false);
        },

        updateProgress: function(percentage) {
            this.progressBar.css('width', percentage + '%');
        }
    };

    $(document).ready(function() {
        UpImmoImport.init();
    });

})(jQuery); 