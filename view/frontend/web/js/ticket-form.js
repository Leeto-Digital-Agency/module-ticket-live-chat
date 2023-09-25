define([
    'jquery', 
    'mage/mage', 
    'Magento_Ui/js/form/form'
], function ($, mage, Form) {
    'use strict';

    return Form.extend({
        initialize: function (config) {
            this._super();
            $(document).ready(function () {
                var uploadedFileNames = config.uploadedFileNames;
                $('input[name="ticket_type"]').on('change', function () {
                    var selectedValue = $(this).val();
                    if (selectedValue == 2) {
                        $('#order_increment_id').attr('required', true);
                        $('#dropdown-field').show();
                    } else {
                        $('#order_increment_id').removeAttr('required');
                        $('#dropdown-field').hide();
                    }
                });

                $('input[name="additional_email"]').on('change', function () {
                    var selectedValue = $(this).val();
                    if (selectedValue === '2') {
                        $('#additional-input-field').show();
                    } else {
                        $('#additional-input-field').hide();
                    }
                });

                $('#attachments').on('change', function (event) {
                    var selectedFiles = event.target.files;

                    // Update uploadedFileNames with new file names
                    for (var i = 0; i < selectedFiles.length; i++) {
                        uploadedFileNames.push(selectedFiles[i].name);
                    }

                    // Update displayed file names
                    $('#uploaded-files-list').text('');
                    $('#uploaded-files-list').text(uploadedFileNames.join(', '));
                    uploadedFileNames = []; // Clear the array for the next selection
                });

                $('#ticket-form').submit(function (event) {
                    var isValid = true;

                    $('.field-error').empty();

                    // Validate radio buttons
                    var radioGroups = $('.radio-group');
                    radioGroups.each(function () {
                        var radioInputs = $(this).find('input[name="ticket_type"], input[name="additional_email"]');
                        var isAnyChecked = radioInputs.is(':checked');

                        if (!isAnyChecked) {
                            var errorMessage = 'Please select an option.';
                            $(this).find('.field-error').text(errorMessage);
                            isValid = false;
                        }
                    });

                    // Validate File Upload
                    var fileInput = $('#attachments');
                    if (fileInput.length) {
                        var allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
                        var files = fileInput[0].files;

                        var maxFileSize = 15 * 1024 * 1024; // 15 MB in bytes
                        var totalFileSize = 0;
                        
                        if (files.length > 5) {
                            $('#attachments-error').text('You can only upload up to five files.');
                            isValid = false;
                        } else {
                            for (var i = 0; i < files.length; i++) {
                                var fileName = files[i].name;
                                var fileExtension = fileName.split('.').pop().toLowerCase();
                                if (allowedExtensions.indexOf(fileExtension) === -1) {
                                    $('#attachments-error').text("Only JPG, JPEG, PNG, PDF, DOC, and DOCX files are allowed.");
                                    isValid = false;
                                    break;
                                }
                                totalFileSize += files[i].size;
                            }
                            if (totalFileSize > maxFileSize) {
                                $('#attachments-error').text('Total file size should not exceed 15 MB.');
                                isValid = false;
                            }
                        }
                    }
                    if (!isValid) {
                        event.preventDefault();
                    } 
                });
            });
        }
    });
});