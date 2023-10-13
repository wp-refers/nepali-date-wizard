(function ($) {

    var NepaliDateWizardManager = {
        init: function () {
            this.cacheDom();
            this.bind();
        },

        cacheDom: function () {
            this.$nepaliDateWizardWrapper = $('.nepali-date-wizard-form-wrapper');
            this.btnSubmit  = this.$nepaliDateWizardWrapper.find('.nepali-date-wizard-submit-form');
        },

        bind: function () {
            this.btnSubmit.on('click', this.xhr);
        },

        xhr: function (e) {
            e.preventDefault()

            var $this = $(this),
                parentEl = $this.parents('.nepali-date-wizard-conversion'),
                formType = $this.val();

            if (formType === 'Reset') {
                parentEl.find('input:not([type="button"])').val('')
                return false;
            }

            $this.prop('disabled', true);

            $.ajax({
                url: nepali_date_wizard_data.ajaxurl,
                type: 'POST',
                data: {
                    security: nepali_date_wizard_data.nonce,
                    action: 'nepali_date_wizard_xhr_action',
                    year: parentEl.find('input[name=year]').val(),
                    month: parentEl.find('input[name=month]').val(),
                    day: parentEl.find('input[name=day]').val(),
                    convertTo: formType === 'Convert BS to AD' ? 'convert-bs-to-ad' : 'convert-ad-to-bs'
                },
                success: function (response) {
                    $('.nepali-date-wizard-result-response').html(response.body);
                    $this.prop('disabled', false);
                }
            });
        }
    }
    NepaliDateWizardManager.init();
}) (jQuery);