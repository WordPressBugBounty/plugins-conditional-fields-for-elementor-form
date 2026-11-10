"use strict";

var formRepeaterFields = [];
var getTagStatus = false;

/**
 * Keep the raw document config free of Backbone objects for nested repeaters.
 *
 * Elementor's BaseSettings::handleRepeaterData() writes a Backbone.Collection back
 * into the attributes object it receives. For a repeater nested inside a repeater
 * row (cfef_repeater_data inside form_fields) that object is the raw row from
 * elementor.documents.getCurrent().config.elements, so the config ends up with a
 * circular Collection. Elementor Pro's editor-templates-extended package walks that
 * config recursively and crashes with "Maximum call stack size exceeded".
 * Passing shallow row copies keeps the raw config untouched.
 * The shared `cfefNestedRepeaterPatched` flag prevents double patching when
 * Cool FormKit is active as well. Wrapped in an IIFE so the helper name does not
 * clash with Cool FormKit's global editor script.
 */
(function () {
    const cfefPatchNestedRepeaterSettings = () => {
        const models = window.elementorModules && elementorModules.editor && elementorModules.editor.elements && elementorModules.editor.elements.models;
        const proto = models && models.BaseSettings && models.BaseSettings.prototype;

        if (!proto || typeof proto.convertRepeaterValueToCollection !== 'function' || proto.cfefNestedRepeaterPatched) {
            return;
        }

        const originalConvert = proto.convertRepeaterValueToCollection;
        const hasNestedRepeater = (control) => Object.values(control.fields || {}).some(
            (field) => field && ('repeater' === field.type || field.is_repeater)
        );

        proto.convertRepeaterValueToCollection = function (attrs, repeaterControl) {
            const rows = attrs ? attrs[repeaterControl.name] : null;

            if (Array.isArray(rows) && hasNestedRepeater(repeaterControl)) {
                const clonedRows = rows.map((row) => (
                    row && 'object' === typeof row && !(row instanceof Backbone.Model) ? Object.assign({}, row) : row
                ));
                attrs = Object.assign({}, attrs, { [repeaterControl.name]: clonedRows });
            }

            return originalConvert.call(this, attrs, repeaterControl);
        };
        proto.cfefNestedRepeaterPatched = true;
    };

    cfefPatchNestedRepeaterSettings();
    window.addEventListener('elementor/init', cfefPatchNestedRepeaterSettings);
})();

function registerCfefRepeaterControlView() {
    var elementorInstance = window.elementor;
    var originalRepeater = elementorInstance && elementorInstance.modules && elementorInstance.modules.controls && elementorInstance.modules.controls.Repeater;

    if (!originalRepeater || elementorInstance.modules.controls.cfefRepeaterPatched) {
        return;
    }

    var RepeaterControlItemView = originalRepeater.extend({
        onBeforeRender: function () {
            if (this.$el.hasClass('elementor-control-cfef_repeater_data')) {
                this.$el[0].addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            }
        },
        onDestroy: function () {
            if (this.$el.closest('.elementor-control-type-form-fields-repeater')) {
                var elements = this.$el.closest('.elementor-repeater-fields-wrapper').find('.elementor-form-field-shortcode');
                setTimeout(function () {
                    if (!getTagStatus) {
                        formRepeaterFields = [];
                        getTagStatus = true;
                        elements.each(function (indx, ele) {
                            formRepeaterFields.push(ele);
                        });
                    }
                });
                setTimeout(function () {
                    if (getTagStatus) {
                        getTagStatus = false;
                    }
                }, 300);
            }
        }
    });

    elementorInstance.addControlView('repeater', RepeaterControlItemView);
    elementorInstance.modules.controls.cfefRepeaterPatched = true;
}

window.addEventListener('elementor/init', registerCfefRepeaterControlView);

if (window.jQuery) {
    jQuery(window).on('elementor:init', registerCfefRepeaterControlView);
}

(function($) {
    "use strict";

    // function for all dynamic tag to show the Id's of all elements
    $( document ).ready( function () {

        jQuery(document).on('click','.elementor-control-form_fields_conditions_tab',(event)=>{
            const mainWrp=jQuery(event.currentTarget).closest('.elementor-repeater-row-controls.editable');
            const addBtn=mainWrp.find('.elementor-control-cfef_repeater_data .elementor-repeater-add');
            jQuery(addBtn).text('+ Add Conditions');
            const logicMeet = mainWrp.find('.elementor-control-cfef_logic_meet .elementor-control-content .elementor-control-field .elementor-control-input-wrapper select option')[1];
            const logicMode = mainWrp.find('.elementor-control-cfef_logic_mode .elementor-control-content .elementor-control-field .elementor-control-input-wrapper .elementor-choices input');
            const showHideFieldLableText = mainWrp.find('.elementor-control-cfef_repeater_data .elementor-control-content label span')
            function updateLabel() {
                for (let i = 0; i < logicMode.length; i++) {
                    if (logicMode[i].checked) {
                        if (logicMode[i].value === 'hide') {
                            showHideFieldLableText[0].textContent = 'Hide fields if';
                        } else {
                            showHideFieldLableText[0].textContent = 'Show fields if';
                        }
                        break;
                    }
                }
            }

            updateLabel();
            logicMode.on('change', updateLabel);
            if (!logicMeet.innerHTML.includes('(PRO)')) {
                logicMeet.innerHTML += ' (PRO)';
                logicMeet.setAttribute('disabled', 'disabled');
                logicMeet.style.backgroundColor = '#00000015';
            }

            const controlsWrp=mainWrp.find('.elementor-control-cfef_logic_field_id')?.closest('.elementor-repeater-row-controls');

            controlsWrp?.addClass('editable');
        });
        jQuery(document).on('mouseenter', '.elementor-repeater-row-controls.editable .elementor-control-cfef_repeater_data .elementor-repeater-fields-wrapper', function() {
            const optionsFields = jQuery('.elementor-repeater-row-controls.editable .elementor-control-cfef_repeater_data .elementor-repeater-fields-wrapper .elementor-repeater-fields .elementor-repeater-row-controls .elementor-control-cfef_logic_field_is .elementor-control-content .elementor-control-field .elementor-control-input-wrapper select option');
            for(let i = 0;i < optionsFields.length;i++){
                if (optionsFields[i].value !== "==" && optionsFields[i].value !== "!=" && optionsFields[i].value !== ">" && optionsFields[i].value !== "<" && !optionsFields[i].innerHTML.includes('(PRO)')) {
                    optionsFields[i].innerHTML += ' (PRO)';
                    optionsFields[i].setAttribute('disabled', 'disabled');
                    optionsFields[i].style.backgroundColor = '#00000015';
                    }
            }
        });


        $("body").on("click",'.elementor-control-tag-area[data-setting="cfef_logic_field_id"]',function(event){
            if( $(this).data("isChecked") != "ok" ) {
              $(this).after('<div class="elementor-control-dynamic-switcher elementor-control-unit-1 cfef-add-tag" data-tooltip="add Tags" original-title=""><i class="eicon-database" aria-hidden="true"></i><span class="elementor-screen-only">Dynamic Tags</span></div>');
              $(this).data("isChecked","ok");
            }
        })

        $("body").on("click",".cfef-add-tag",function(event){
           var idList ='<ul class="cfef-dynamic-tag">';
           var formWrapper = $(this).closest(".elementor-repeater-fields-wrapper").parents(".elementor-repeater-fields-wrapper");
           $(".elementor-form-field-shortcode",formWrapper).each(function() {
            const regexPattren = /\".*?\"/gm;
            let matches;
            var fieldName = $(this).val();
            while ((matches = regexPattren.exec(fieldName)) !== null) {
                if (matches.index === regexPattren.lastIndex) {
                    regexPattren.lastIndex++;
                }
                matches.forEach((match) => {
                    fieldName = match.replaceAll('"', "");
                });
            }
            $(this).data("actual-id", fieldName);

            idList += '<li title="Field ID" data-field-id="' + fieldName + '">' + fieldName + '</li>';
           });
          idList +='</ul>';
          $(this).closest(".elementor-control-input-wrapper").append(idList);
        })

        $(document).mouseup(function(event)
        {
            var container = $(".cfef-dynamic-tag");

            if (!container.is(event.target) && container.has(event.target).length === 0)
            {
                container.hide();
            }
        });

            $("body").on("click", ".cfef-dynamic-tag li", function (event) {
                var selectedValue = $(this).data("field-id");
                $(this).parent().siblings().val(selectedValue);
                $(this).parent().siblings().trigger('input');
                if($('#elementor-panel-saver-button-publish')[0].classList.contains('elementor-disabled')){
                    $('#elementor-panel-saver-button-publish')[0].classList.remove('elementor-disabled');
                }
                setTimeout(function () {
                    $(".cfef-dynamic-tag").remove();
                }, 500);
        });

        jQuery(document).on('click','#cfef_elementor_review_dismiss',(event)=>{
            jQuery(".cfef_elementor_review_notice").hide();
            const btn=jQuery(event.target);
            const nonce=btn.data('nonce');
            const url=btn.data('url');

            jQuery.ajax({
                type: 'POST',
                url: url,
                data: {
                    action: 'cfef_elementor_review_notice',
                    cfef_notice_dismiss: true,
                    nonce: nonce
                },
                success: (response) => {
                    btn.closest('.elementor-control').remove();
                }
            });
        });
    })
})(jQuery);
