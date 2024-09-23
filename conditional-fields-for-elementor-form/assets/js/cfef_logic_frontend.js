(function($) {
    "use strict";
    
    $(document).ready(function() {
    
        // function for compare conditional values 
        function checkFieldLogic(compareFieldValue, conditionOperation, compareValue) {            

            conditionOperation = decodeHTMLEntities(conditionOperation);
            compareValue = decodeHTMLEntities(compareValue).trim();
            compareFieldValue = compareFieldValue.trim();

            var values = compareFieldValue.split(',');

            var matchFound = values.some(function(value) {
                return value.trim() === compareValue;
            });

            switch (conditionOperation) {
                case "==":
                    return matchFound && '' !== compareFieldValue;
                case "!=":
                    return !matchFound && compareFieldValue !== "";
                case ">":
                    return parseInt(compareFieldValue) > parseInt(compareValue);
                case "<":
                    return parseInt(compareFieldValue) < parseInt(compareValue);
                default:
                    return false;
            }
        }
        
        function decodeHTMLEntities(text) {
            var textArea = document.createElement('textarea');
            textArea.innerHTML = text;
            return textArea.value;
        }

        // function to add hidden class when form load
        function addHiddenClass(form) {
            var logicData = $(".cfef_logic_data_js", form).val();
            if (logicData && logicData !== "undefined") {
                try {
                    logicData = jQuery.parseJSON(logicData);
                    $.each(logicData, function(logic_key, logic_value) {
                        if ($(".elementor-field-group-" + logic_key).hasClass("elementor-field-type-html")) {
                                
                            field = $(".elementor-field-group-" + logic_key).closest(".elementor-field-group");
                        
                        } else {
                            var field = getFieldMainDivById(logic_key);
                        }
                        var displayMode= logic_value.display_mode;
                        var fireAction = logic_value.fire_action;
                        var conditionPassFail = [];
                        $.each(logic_value.logic_data, function(conditional_logic_key, conditional_logic_values) {
                            if(conditional_logic_values.cfef_logic_field_id)
                            {
                                var value_id = getFieldEnteredValue(conditional_logic_values.cfef_logic_field_id, form);
                                conditionPassFail.push(checkFieldLogic(value_id, conditional_logic_values.cfef_logic_field_is, conditional_logic_values.cfef_logic_compare_value));

                            }
                        }); 

                        var conditionResult = fireAction == "All" ? conditionPassFail.every(function(fvalue) { return fvalue === true; }) : conditionPassFail.some(function(fvalue) { return fvalue === true; });
                        if (displayMode== "show") {
                            if (conditionResult) {
                            } else {
                                field.addClass("cfef-hidden");
                            }
                        } else {
                            if (conditionResult) {
                                field.addClass("cfef-hidden");
                            } else {
                            }
                        }
                    });
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                }
            }
            else {
            // console.warn("JSON data is empty or undefined");
            }
        }
        
        
        // function to check all the conditions valid or not . and based on that condition shosw and hide the fields 
        function logicLoad(form) {
            var logicData = $(".cfef_logic_data_js", form).val();
            if (logicData && logicData !== "undefined") {
                try {
                        logicData = jQuery.parseJSON(logicData);
                        $.each(logicData, function(logic_key, logic_value) {
                            if ($(".elementor-field-group-" + logic_key).hasClass("elementor-field-type-html")) {
                                    
                                field = $(".elementor-field-group-" + logic_key).closest(".elementor-field-group");
                            
                            } else {
                                if(jQuery('.elementor-field-group-'+logic_key).hasClass('elementor-field-type-step')){
                                    setTimeout(()=>{
                                        jQuery('.elementor-field-group-'+logic_key).find('.e-form__buttons .elementor-field-type-next .elementor-button').attr('id','form-field-'+logic_key)

                                        var field = getFieldMainDivById(logic_key);
                                        performFieldLogic(field, logic_value, form, logic_key)
                                    },500)
                                }

                                var field = getFieldMainDivById(logic_key);
                                performFieldLogic(field, logic_value, form, logic_key)
                            }
                            performFieldLogic(field, logic_value, form, logic_key)
                        });
                    } catch (e) {
                        console.error("Error parsing JSON:", e);
                        }
            } 
            else {
            // console.warn("JSON data is empty or undefined");
            }
        }
        
        function performFieldLogic(field, logic_value, form, logic_key){
            var displayMode= logic_value.display_mode;
            var fireAction = logic_value.fire_action;
            var file_types = logic_value.file_types;

            var conditionPassFail = [];

            $.each(logic_value.logic_data, function(conditional_logic_key, conditional_logic_values) {
                var dependent_fi = $(".elementor-field-group-" + conditional_logic_values.cfef_logic_field_id, form);
                if(dependent_fi.hasClass('elementor-field-group-acceptance') || dependent_fi.hasClass('elementor-field-type-acceptance')){
                        dependent_fi.find('.elementor-field-subgroup .elementor-field-option input').click(()=>{
                                if(dependent_fi.find('.elementor-field-subgroup .elementor-field-option input')[0].checked === true){
                                    dependent_fi.find('.elementor-field-subgroup .elementor-field-option input').val('off') 
                                }else{
                                    dependent_fi.find('.elementor-field-subgroup .elementor-field-option input').val('on')
                                }
                                })
                }

                var hiddenDiv = dependent_fi[0];
                var	is_field_hidden = true;
                                    
                is_field_hidden = hiddenDiv?.classList.contains('cfef-hidden');
                if(conditional_logic_values.cfef_logic_field_id){
                    var value_id = getFieldEnteredValue(conditional_logic_values.cfef_logic_field_id, form);
                    var value = is_field_hidden ? false : checkFieldLogic(value_id, conditional_logic_values.cfef_logic_field_is, conditional_logic_values.cfef_logic_compare_value);
                    conditionPassFail.push(value);
                }
                                   
            });

            var conditionResult = fireAction == "All" ? conditionPassFail.every(function(fvalue) { return fvalue === true; }) : conditionPassFail.some(function(fvalue) { return fvalue === true; });
            if (displayMode== "show") {
                if (conditionResult) {
                    field.removeClass("cfef-hidden");
                    if(field.hasClass('elementor-field-required')){
                    logicFixedRequiredShow(field,file_types);
                    }
                } else {
                    field.addClass("cfef-hidden");
                    if(field.hasClass('elementor-field-required')){
                        logicFixedRequiredHidden(field, logic_key,file_types);
                    } 
                }
            } else {
                if (conditionResult) {
                    field.addClass("cfef-hidden");
                    if(field.hasClass('elementor-field-required')){
                        logicFixedRequiredHidden(field, logic_key,file_types);
                    }
                } else {
                    field.removeClass("cfef-hidden");
                    if(field.hasClass('elementor-field-required')){
                        logicFixedRequiredShow(field,file_types);
                    }
                }
            }
        }

        function logicFixedRequiredShow(formField,file_types) {
            if (formField.hasClass("elementor-field-type-radio") && formField.find('input[value="^newOptionTest"]').length !== 0) {
                formField.find('input[value="^newOptionTest"]').closest("span.elementor-field-option").remove();
            } else if (formField.hasClass("elementor-field-type-acceptance")) {
                const acceptanceInput = formField.find('.elementor-field-subgroup .elementor-field-option input')[0]
                if (acceptanceInput && acceptanceInput.checked === true) {
                }
            } else if (formField.hasClass("elementor-field-type-checkbox") && formField.find('input[value="newchkTest"]').length !== 0) {
                formField.find('input[value="newchkTest"]').closest("span.elementor-field-option").remove();
            } else if (formField.hasClass("elementor-field-type-date") && formField.find("input").val() === "1003-01-01") {
                formField.find("input").val("");
            } else if (formField.hasClass("elementor-field-type-time") && formField.find("input").val() === "11:59") {
                formField.find("input").val("");
            } else if (formField.hasClass("elementor-field-type-tel") && formField.find("input").val() === "+1234567890") {
                formField.find("input").val("");
            } else if (formField.hasClass("elementor-field-type-url") && formField.find("input").val() === "https://testing.com") {
                formField.find("input").val("");
            } else if (formField.hasClass("elementor-field-type-email") && formField.find("input").val() === "cool_plugins@abc.com") {
                formField.find("input").val("");
            } else if (formField.hasClass("elementor-field-type-number") && formField.find("input").val() === "000") {
                formField.find("input").val("");
            } 
            else if (formField.hasClass("elementor-field-type-upload")) {
                const firstType = file_types.split(',')[0];
                const inputField=formField.find('input');
                const fileName = `${my_script_vars.pluginConstant}assets/images/placeholder.${firstType}`;
                const inputValue=inputField.val();
                if(inputValue.indexOf(fileName) !== -1){
                    inputField.val('');
                }
            }
            else if (formField.hasClass("elementor-field-type-textarea") && formField.find("textarea").val() === "cool_plugins") {
                formField.find("textarea").val("");
            } else if (formField.hasClass("elementor-field-type-select")) {
                var selectBox = formField.find("select");
                if (selectBox.length > 0 && selectBox.find("option").length > 0) {
                    var selectedValue = selectBox.val();
                    if (selectedValue == 'premium1@') {
                        selectBox.find("option[value='premium1@']").remove();
                        selectBox.val(selectBox.find("option:first").val());
                    }
                }
            } else {
                var FieldValues = formField.find("input").val();
                if (FieldValues == "cool23plugins") {
                    formField.find("input").val("");
                }
            }
        }
        
            // Add the default value when form Field is hidden
        function logicFixedRequiredHidden(formField, fieldKey, file_types) {
            if (formField.hasClass("elementor-field-type-radio")) {
                var groupclass = '.elementor-field-group-' + fieldKey;
                const field2 = $(groupclass);

                if (field2.length > 0) {
                    if (field2.find('input[value="^newOptionTest"]').length === 0) {
                        const newOption = $(`
                            <span class="elementor-field-option">
                                <input type="radio" value="^newOptionTest" id="form-field-newOption" name="form_fields[${fieldKey}]" required="required" aria-required="true" checked="checked">
                            </span>
                        `);
                        field2.find('.elementor-field-subgroup').append(newOption);
                    }
                }
            } else if (formField.hasClass("elementor-field-type-acceptance")) {
                const acceptanceInput = formField.find('.elementor-field-subgroup .elementor-field-option input')[0]
                if (acceptanceInput) {
                    acceptanceInput.checked = true;
                }
            } else if (formField.hasClass("elementor-field-type-checkbox")) {
                var groupclass = '.elementor-field-group-' + fieldKey;
                const field2 = $(groupclass);

                if (field2.length > 0) {
                    if (field2.find('input[value="newchkTest"]').length === 0) {
                        const newOption = $(`
                            <span class="elementor-field-option"><input type="checkbox" value="newchkTest" id="form-field-newchkTest" name="form_fields[${fieldKey}][]" checked="checked"> </span>
                        `);
                        field2.find('.elementor-field-subgroup').append(newOption);
                    }
                }
            } 
            else if (formField.hasClass("elementor-field-type-date")) {
                if(formField.find("input.flatpickr-mobile[type='date']")){
                    let inputField = formField.find("input.flatpickr-mobile");
                    inputField.attr("type", "text");
                }
                formField.find("input").val("1003-01-01");
            } 
            else if (formField.hasClass("elementor-field-type-time")) {
                formField.find("input").val("11:59");
            } else if (formField.hasClass("elementor-field-type-tel")) {
                // Remove the pattern attribute
                formField.find("input").removeAttr("pattern");
                formField.find("input").val("+1234567890");
            } else if (formField.hasClass("elementor-field-type-url")) {
                formField.find("input").val("https://testing.com");
            } else if (formField.hasClass("elementor-field-type-email")) {
                formField.find("input").val("cool_plugins@abc.com");
            } 
            else if (formField.hasClass("elementor-field-type-upload")) {
                const firstType = file_types.split(',')[0];
                const fileName = `${my_script_vars.pluginConstant}assets/images/placeholder.${firstType}`; // Set the desired filename
                const defaultImage = new File([], fileName, { type: 'image/png' });
                const fileInput = formField.find('input[type="file"]');
                
                // Create a DataTransfer object to handle file operations
                const container = new DataTransfer();
                container.items.add(defaultImage);
                
                // Set the files property of the file input field to the default image
                fileInput[0].files = container.files;
            }
            else if (formField.hasClass("elementor-field-type-number")) {
                var FieldValues = formField.find("input").val();
                var field_obj = formField.find("input");
                var max_v = parseInt(field_obj.attr('max'));
                var min_v = parseInt(field_obj.attr('min'));
                if (!isNaN(min_v)) {
                    formField.find("input").val(min_v + 1);
                } else if (!isNaN(max_v)) {
                    formField.find("input").val(max_v - 1);
                } else {
                    formField.find("input").val("000");
                }
            } else if (formField.hasClass("elementor-field-type-textarea")) {
                formField.find("textarea").val("cool_plugins");
            } else if (formField.hasClass("elementor-field-type-select")) {
                var selectBox = formField.find("select");
                var optionText = 'Premium1@';
                var optionValue = 'premium1@';
                if (selectBox.length > 0 && selectBox.find("option").length > 0) {
                    var optionToRemove = selectBox.find("option[value='premium']");
                    if (optionToRemove.length <= 0) {
                        selectBox.append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                    selectBox.val(optionValue);
                }
            } else if (formField.hasClass("elementor-field-type-text")) {
                formField.find("input").val("cool23plugins");
            } else {
                const inputField=formField.find("input");
                if(inputField.length > 0){
                    const inputId=inputField[0].id
                    jQuery(`#${inputId}`)[0].setAttribute('value','cool23plugins')
                }
                // formField.find("input").val("cool23plugins");
            }
        }

                // Function to get the value of the conditional field 
        function getFieldEnteredValue(id = "", form = "body") {
            var inputValue = "";
            var fieldGroup = $(".elementor-field-group-" + id, form);

            if (fieldGroup.hasClass("elementor-field-type-radio")) {
                inputValue = fieldGroup.find("input:checked").val();
            } else if (fieldGroup.hasClass("elementor-field-type-checkbox")) {
                var multiValue = [];

                // Check if any checkbox is checked
                var checkboxes = fieldGroup.find("input[type='checkbox']:checked");
                if (checkboxes.length > 0) {
                    // Collect values of checked checkboxes
                    checkboxes.each(function() {
                        multiValue.push($(this).val());
                    });
                    inputValue = multiValue.join(", ");
                } else {
                    // No checkbox is checked
                    inputValue = id;
                }
            } else if (fieldGroup.hasClass("elementor-field-type-select")) {
                inputValue = fieldGroup.find("select").val();
                if(fieldGroup.find("select")[0].multiple){
                    inputValue=inputValue.join(', ');
                }
            } else if (fieldGroup.hasClass("elementor-field-type-textarea")) {
                inputValue = fieldGroup.find("textarea").val();
            } else {
                inputValue = fieldGroup.find("input").val();
            }

            return inputValue === undefined ? '' : inputValue;
        }
        
        // function to get the id of the conditional field 
        function getFieldMainDivById(id = "") {
            if ($("#form-field-" + id).length > 0) {
                return $("#form-field-" + id).closest(".elementor-field-group");
            } else {
                return $("#form-field-" + id + "-0").closest(".elementor-field-group");
            }
        }

        //add conditional fields on popup form when page load
        $(document).on('elementor/popup/show', function() {
            $(".elementor-form").each(function() {
                var form = $(this).closest(".elementor-widget-container");
                addHiddenClass(form);
                logicLoad(form);
            });
        });

     //add conditional fields on form when page load
        window.addEventListener('elementor/frontend/init', function() {
            $(".elementor-form").each(function() {
                var form = $(this).closest(".elementor-widget-container");
                 addHiddenClass(form);
                 logicLoad(form);
            });
        });

        // Update form filed hidden status after form submit
        jQuery(document).on('submit_success', function(e, data) {
            setTimeout(()=>{
                    var form = jQuery(e.target).closest(".elementor-widget-container");
                    logicLoad(form);
            },200)
        });


        // validate condtions when any changes apply to any form fields
        $("body").on("input change", ".elementor-form input, .elementor-form select, .elementor-form textarea", function(e) {
            var form = $(this).closest(".elementor-widget-container");
            logicLoad(form);
        });

    

    });

})(jQuery);
