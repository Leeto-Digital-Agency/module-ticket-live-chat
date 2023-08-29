define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this.setupElements();
            this.attachEventHandlers();
        },

        setupElements: function () {
            this.chatButton = $('#chat-container .chat-button').first();
            this.chatPopup = $('#chat-container .chat-popup').first();
            this.chatMessages = $('#chat-container .chat-messages').first();
            this.chatTextarea = $('#chat-container .chat-input textarea').first();
            this.chatInput = $('#chat-container .chat-input').first();
            this.chatWrapper = $('#chat-container .chat-wrapper');
            this.attachIcon = $('#chat-container .attach-icon').first();
            this.fileInput = $('#chat-container #file-input').first();
            this.emailInputSection = $('#chat-container .email-input-section').first();

            this.chatWrapper.hide(); // hide chat messages
            this.chatInput.hide(); // hide chat textarea
        },

        attachEventHandlers: function () {
            var self = this;

            this.chatButton.on('click', function () {
                self.toggleChatPopup();
            });

            $('#chat-container .close-button').on('click', function () {
                self.closeChatPopup();
            });

            $('#chat-container .send-icon').on('click', function () {
                self.sendMessage();
            });

            $('#continue-button').on('click', function (e) {
                e.preventDefault();
                if (!self.validateEmail()) {
                    return;
                }
                self.showChatMessages();
            });

            this.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    self.sendMessage();
                } else if (event.key === 'Enter' && event.shiftKey) {
                    event.preventDefault();
                    self.handleTextareaShiftEnter(event);
                }
            });

            this.chatTextarea.on('input', function () {
                self.adjustTextareaHeight();
            });

            this.fileInput.on('change', function () {
                self.handleFileAttachment();
            });
        },

        toggleChatPopup: function () {
            this.chatPopup.toggleClass('active');
            this.chatButton.toggleClass('active');
        },
        
        showChatMessages: function () {
            this.emailInputSection.hide(); // Hide email input form
            this.chatWrapper.show(); // show chat messages
            this.chatInput.show(); // show chat textarea
        },

        closeChatPopup: function () {
            this.chatPopup.removeClass('active');
        },

        sendMessage: function () {
            this.clearErrorMessage();
            const MAX_MESSAGE_LENGTH = 2000;
            let messageText = this.chatTextarea.val();

            if (messageText.length > MAX_MESSAGE_LENGTH) {
                let errorMessage = $('<div class="error-message"><div class="text">Message exceeds the maximum length of ' + MAX_MESSAGE_LENGTH + ' characters.</div></div>');
                this.chatMessages.append(errorMessage);
                this.scrollToBottom();
                return;
            }

            if (messageText.trim() !== '') {
                let message = $('<div class="message user-message"><div class="text"></div></div>');
                message.find('.text').html(messageText.replace(/\n/g, '<br>'));
                this.chatMessages.append(message);
                this.scrollToBottom();
                this.chatTextarea.val('');
                this.adjustTextareaHeight();
            }
        },

        handleTextareaShiftEnter: function (event) {
            let startPos = this.chatTextarea[0].selectionStart;
            let endPos = this.chatTextarea[0].selectionEnd;

            let text = this.chatTextarea.val();
            let newText = text.substring(0, startPos) + '\n' + text.substring(endPos);

            this.chatTextarea.val(newText);
            this.chatTextarea[0].selectionStart = this.chatTextarea[0].selectionEnd = startPos + 1;
            this.adjustTextareaHeight();
        },

        adjustTextareaHeight: function () {
            let lines = this.chatTextarea.val().split('\n');
            this.chatTextarea[0].style.height = '32px';

            let lineHeight = this.chatTextarea.prop('scrollHeight') / lines.length;
            let requiredHeight = (lines.length * lineHeight) - 1;

            if (requiredHeight > this.chatTextarea[0].clientHeight) {
                this.chatTextarea[0].style.height = requiredHeight + 'px';
            }
        },

        handleFileAttachment: function () {
            this.clearErrorMessage();
            let file = this.fileInput[0].files[0];

            if (!this.validateFile(file)) {
                let errorMessage = $('<div class="error-message"><div class="text">Invalid file type or file size exceeds the limit.</div></div>');
                this.chatMessages.append(errorMessage);
                this.scrollToBottom();
                this.fileInput.val('');
                return;
            }

            let message = this.createFileMessage(file);
            this.chatMessages.append(message);
            this.scrollToBottom();
        },

        createFileMessage: function (file) {
            let message = $('<div class="message user-message"><div class="text"></div></div>');
            let fileContent = $('<div class="file-content"></div>');

            if (file.type.startsWith('image/')) {
                let fileImageContent = $('<a><p class="file-name"></p><img class="file-image"></a>');
                let image = fileImageContent.find('.file-image');
                let fileName = fileImageContent.find('.file-name');

                fileImageContent.attr('href', URL.createObjectURL(file));
                fileImageContent.attr('target', '_blank');
                image.attr('src', URL.createObjectURL(file));
                fileName.text(file.name);
                fileContent.append(fileImageContent);
            } else {
                let fileName = $('<a class="file-name"></a>');

                fileName.text(file.name);
                fileName.attr('href', URL.createObjectURL(file));
                fileName.attr('download', file.name);
                fileContent.append(fileName);
            }

            message.find('.text').append(fileContent);
            return message;
        },

        validateFile: function (file) {
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            let maxFileSize = 3 * 1024 * 1024; // 3 MB in bytes
            let fileNameParts = file.name.split('.');
            let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();

            return allowedExtensions.includes(fileExtension) && file.size <= maxFileSize;
        },
        validateEmail: function () {
            let email = $('#email-input').val();
            let validateErrorMessage = '';
            let isValid = true;
            let emailMessage = $("#email-error-message");
            emailMessage.text('');

            if (email === '') {
                validateErrorMessage = 'Email is required.';
                isValid = false;
            } else if (!this.validatePattern(email)) {
                validateErrorMessage = 'Invalid email format.';
                isValid = false;
            }
            if (!isValid) {
                emailMessage.show();
                emailMessage.text(validateErrorMessage);
            }
            return isValid;
        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        },
        validatePattern: function (email) {
            let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailPattern.test(email);
        },
        scrollToBottom: function () {
            this.chatWrapper[0].scrollTop = this.chatMessages.prop('scrollHeight');
        }
    });
});
