(function ($) {

    /* -------------------------------------------------
     * NOTICE DISMISS HANDLER (Merged)
     * ------------------------------------------------- */
    $(document).on(
        'click',
        '.cfef-dismiss-notice, .cfef-dismiss-cross, .cfef-tec-notice .notice-dismiss, [data-notice_id="formdb-marketing-elementor-form-submissions"] .e-notice__dismiss',
        function (e) {
            e.preventDefault();

            let $el = $(this);
            let noticeType = $el.data('notice');
            let nonce = $el.data('nonce');

            // Fallback for Form DB marketing notices
            if (!noticeType && typeof cfefFormDBMarketing !== 'undefined') {
                noticeType = cfefFormDBMarketing.formdb_type;
                nonce = cfefFormDBMarketing.formdb_dismiss_nonce;
            }

            // Fallback for TEC notice
            if (!noticeType) {
                noticeType = $('.cfef-tec-notice').data('notice');
                nonce = $('.cfef-tec-notice').data('nonce');
            }

            if (!noticeType || !nonce) return;

            $.post(ajaxurl, {
                action: 'cfef_mkt_dismiss_notice',
                notice_type: noticeType,
                nonce: nonce
            }, function (response) {
                if (response.success) {

                    if (noticeType === 'cool_form') {
                        $el.closest('.cool-form-wrp').fadeOut();
                    } else if (noticeType === 'tec_notice') {
                        $el.closest('.cfef-tec-notice').fadeOut();
                    }
                }
            });
        }
    );

    /* -------------------------------------------------
     * INSTALL PLUGIN HANDLER (Merged)
     * ------------------------------------------------- */
    function installPlugin(btn, slugg) {

        let button = $(btn);
        let $wrapper = button.closest('.cool-form-wrp');

        const slug = getPluginSlug(slugg);

        if (!slug) return;

        const allowedSlugs = [
            'extensions-for-elementor-form',
            'conditional-fields-for-elementor-form',
            'country-code-field-for-elementor-form',
            'loop-grid-extender-for-elementor-pro',
            'events-widgets-for-elementor-and-the-events-calendar',
            'conditional-fields-for-elementor-form-pro',
            'sb-elementor-contact-form-db',
        ];
        if (!slug || !allowedSlugs.includes(slug)) return;

        let nonce =
            button.data('nonce') ||
            (typeof cfefFormDBMarketing !== 'undefined' ? cfefFormDBMarketing.nonce : null);

        button.text('Installing...').prop('disabled', true);
        disableAllOtherPluginButtonsTemporarily(slug);

        $.post(ajaxurl, {
            action: 'cfef_install_plugin',
            slug: slug,
            _wpnonce: nonce
        }, function (response) {

            const responseString = JSON.stringify(response);
            const responseContainsPlugin = responseString.includes(slug);

            // Special case: Country Code plugin
            if (slug === 'country-code-field-for-elementor-form') {
                const $pageHtml = $(response);
                let $input = $pageHtml.find('input[name="country_code"]');

                if ($input.is(':disabled')) {
                    showNotActivatedMessage($wrapper);
                } else {
                    handlePluginActivation(button, slug, $wrapper);
                }
                return;
            }

            if (responseContainsPlugin) {
                handlePluginActivation(button, slug, $wrapper);

                if (
                    typeof cfefFormDBMarketing !== 'undefined' &&
                    cfefFormDBMarketing.redirect_to_formdb
                ) {
                    window.location.href = 'admin.php?page=formsdb';
                }

            } else {
                showNotActivatedMessage($wrapper);
            }
        });
    }

    /* -------------------------------------------------
     * HELPERS
     * ------------------------------------------------- */

    function handlePluginActivation(button, slug, $wrapper) {
        button
            .text('Activated')
            .removeClass('e-btn e-info e-btn-1 elementor-button-success')
            .addClass('elementor-disabled')
            .prop('disabled', true);

        disableOtherPluginButtons(slug);

        let successMessage = 'Save & reload the page to start using the feature.';

        if (slug === 'events-widgets-for-elementor-and-the-events-calendar') {
            successMessage =
                'Events Widget is now active! Design your Events page with Elementor to access powerful new features.';
            $('.cfef-tec-notice .ect-notice-widget').text(successMessage);
        } else {
            $wrapper.find('.elementor-control-notice-success').remove();
            $wrapper.find('.elementor-control-notice-main-actions').after(
                '<div class="elementor-control-notice elementor-control-notice-success">' +
                '<div class="elementor-control-notice-content">' +
                successMessage +
                '</div></div>'
            );
        }
    }

    function showNotActivatedMessage($wrapper) {
        $wrapper.find('.elementor-control-notice-success').remove();
        $wrapper.find('.elementor-control-notice-main-actions').after(
            '<div class="elementor-control-notice elementor-control-notice-success">' +
            '<div class="elementor-control-notice-content">' +
            'The plugin is installed but not yet activated. Please go to the Plugins menu and activate it.' +
            '</div></div>'
        );
    }

    function getPluginSlug(plugin) {
        const slugs = {
            'cool-form-lite': 'extensions-for-elementor-form',
            'conditional': 'conditional-fields-for-elementor-form',
            'country-code': 'country-code-field-for-elementor-form',
            'loop-grid': 'loop-grid-extender-for-elementor-pro',
            'events-widget': 'events-widgets-for-elementor-and-the-events-calendar',
            'form-db': 'sb-elementor-contact-form-db',
        };
        return slugs[plugin];
    }

    function disableAllOtherPluginButtonsTemporarily(activeSlug) {
        const relatedSlugs = [
            'extensions-for-elementor-form',
            'conditional-fields-for-elementor-form',
            'country-code-field-for-elementor-form'
        ];

        $('.cfef-install-plugin').each(function () {
            const $btn = $(this);
            const btnSlug = getPluginSlug($btn.data('plugin'));

            if (btnSlug !== activeSlug && relatedSlugs.includes(btnSlug)) {
                $btn.prop('disabled', true);
            }
        });
    }

    function disableOtherPluginButtons(activatedSlug) {
        const relatedSlugs = [
            'extensions-for-elementor-form',
            'conditional-fields-for-elementor-form',
            'country-code-field-for-elementor-form'
        ];

        if (!relatedSlugs.includes(activatedSlug)) return;

        $('.cfef-install-plugin').each(function () {
            const $btn = $(this);
            const btnSlug = getPluginSlug($btn.data('plugin'));

            if (btnSlug !== activatedSlug && relatedSlugs.includes(btnSlug)) {
                $btn
                    .text('Already Installed')
                    .addClass('elementor-disabled')
                    .prop('disabled', true)
                    .removeClass('e-btn e-info e-btn-1 elementor-button-success');

                $btn.closest('.cool-form-wrp').hide();

                if (btnSlug === 'country-code-field-for-elementor-form') {
                    $('[data-setting="cfef-mkt-country-conditions"]').closest('.elementor-control').hide();
                }
                if (btnSlug === 'conditional-fields-for-elementor-form') {
                    $('[data-setting="cfef-mkt-conditional-conditions"]').closest('.elementor-control').hide();
                }
            }
        });
    }

    if(typeof elementor !== 'undefined' && elementor) {

        const callbackfunction = elementor.modules.controls.BaseData.extend({
            onRender:(data)=>{
                if(!data.el) return;

                const customNotice=data.el.querySelector('.cool-form-wrp');

                if(!customNotice) return;

                const installBtns=data.el.querySelectorAll('button.cfef-install-plugin');

                if(installBtns.length === 0) return;

                installBtns.forEach(btn=>{
                    const installSlug=btn.dataset.plugin;
                    btn.addEventListener('click',()=>{
                        installPlugin(jQuery(btn),installSlug)
                    });
                });
            },
        });

        // Initialize when Elementor is ready
        $(window).on('elementor:init', function () { 
            elementor.addControlView('raw_html', callbackfunction);
        });
    }else{


        $(document).ready(function ($) {

            const customNotice = $('.cool-form-wrp, .cfef-tec-notice, [data-notice_id="formdb-marketing-elementor-form-submissions"], .e-form-submissions-search');

            if(customNotice.length === 0) return;

            const installBtns = customNotice.find('button.cfef-install-plugin, a.cfef-install-plugin');

            if(installBtns.length === 0) return;  
            

            installBtns.each(function(){
                const btn = this;
                const installSlug = btn.dataset.plugin;

                $(btn).on('click', function(){
                    if(installSlug) {
                        installPlugin($(btn), installSlug);
                    } else {
                        installPlugin($(btn), cfefFormDBMarketing.plugin);
                    }
                });
            });
        })
    }

})(jQuery);