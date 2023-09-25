define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, Component, $t, modal) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            this._super();
            this.setupElements();
            this.attachEventHandlers();
            this.ticketId = $("#ticket-user-container").attr('data-ticket-id');
            this.isTicketClosed = false;
            this.ticketControllerUrl = config.ticketControllerUrl;
            this.messageControllerUrl = config.messageControllerUrl;
            this.addAdminMessageControllerUrl = config.addAdminMessageControllerUrl;
            this.openTicketStatusUrl = config.openTicketStatusUrl;
            $('#ticket-user-container .chat-area').hide();
            this.displayChatAreaWithData(this);
        },

        setupElements: function () {
            this.chatArea = $('#ticket-user-container .chat-area');
            this.chatMessages = $('#ticket-user-container .chat-messages');
            this.chatTextarea = $('#ticket-user-container .chat-input textarea');
            this.fileInput = $('#ticket-user-container #user-file-input');
            this.loadingDiv = $('#ticket-user-container .loading-container');
            this.ticketMessage = $('#ticket-user-container');
            this.uploadedFiles = $('#uploaded-files-list');
            this.confirmationModal = $('#confirmationModal');
        },

        attachEventHandlers: function () {
            var self = this;

            $('.chat-input button').on('click', function (event) {
                if (self.isTicketClosed) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: "Do you want to reopen the ticket?",
                        modalClass: "confirmation-modal"
                    };
            
                    modal(options, self.confirmationModal);
            
                    self.confirmationModal.modal('openModal');

                    $('#confirmButton').on('click', function() {
                        self.reopenTicket(self.ticketId, function (event) {
                            self.handleSendMessage(event);
                        });
                        self.confirmationModal.modal('closeModal');
                    });
                } else {
                    self.handleSendMessage(event);
                }
            });

            this.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    self.handleTextareaShiftEnter(event);
                }
            });

            this.chatTextarea.on('input', function () {
                self.adjustTextareaHeight();
            });
            this.fileInput.on('change', function (event) {
                self.updateUploadFilesNames(event);
            });
            this.ticketMessage.on('click', '.ticket-message', function() {
                const $element = $(this);
                $element.find('.detailed-info').toggleClass('active');
                var detailedInfo = this.querySelector('.detailed-info');
                if (detailedInfo.style.maxHeight) {
                    detailedInfo.style.maxHeight = null;
                } else {
                    detailedInfo.style.maxHeight = detailedInfo.scrollHeight + "px";
                }
            });
        },
        displayChatAreaWithData: async function (event) {
            this.fetchChatHeaderData(this.ticketId);
            this.chatArea.css('display', 'flex');
            this.loadingDiv.remove();
            this.scrollToBottom();
        },
        fetchChatHeaderData: function (ticketId) {
            var self = this;

            $.ajax({
                url: this.ticketControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: ticketId 
                },
                success: function (response) {
                    self.updateChatHeader(response);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        updateChatHeader: function (data) {
            var orderLinkElement = $('.ticket-type');
            var subjectElement = $('.ticket-subject .subject');
            var createdAtElement = $('.ticket-date .date');
            var statusElement = $('.ticket-status');
            var statusElementLabel = $('.ticket-status .status');
            var self = this;

            if (data.isOrder) {
                orderLinkElement.find('.order-link-wrapper').show();
                orderLinkElement.find('.title').text(data.ticketType + ':');
                orderLinkElement.find('.order-link-wrapper').find('.order-link').attr("href", data.orderLink);
                orderLinkElement.find('.order-link-wrapper').find('.order-link').text(data.orderIncrementId);
            } else {
                orderLinkElement.find('.title').text('General');
                orderLinkElement.find('.order-link-wrapper').hide();
            }
            subjectElement.text(data.subject);
            createdAtElement.text(data.createdAt);
            statusElementLabel.remove();
            var statusDiv = $('<span class="status"></span>');
            statusDiv.text(data.status);
            if (data.loggedInRequired) {
                self.chatTextarea.attr('disabled', true);
                self.chatTextarea.attr('placeholder', data.message);
                self.fileInput.attr('disabled', true);
                $('.chat-input button').addClass('disabled');
            }
            if (data.isTicketOpened) {
                statusDiv.addClass('active');
            } else if (data.isTicketPending) {
                statusDiv.addClass('pending');
            } else if (data.isTicketClosed) {
                self.isTicketClosed = true;
                statusDiv.addClass('closed');
            }
            statusElement.append(statusDiv);
        },
        handleSendMessage: async function (event) {
            this.clearErrorMessage();
            let messageText = this.chatTextarea.val().trim();
            let files = this.fileInput[0].files;
            var self = this;
            messageText = messageText.replace(/\n/g, '<br>');
            let filesData = await this.getFiles(files);

            if (this.validateTextarea()) {
                return;
            }
            if (files.length > 0 && !filesData) {
                self.fileInput.val('');
                self.uploadedFiles.text('');
                return;
            }
            $.ajax({
                url: this.addAdminMessageControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: self.ticketId,
                    message: messageText,
                    files_data: JSON.stringify(filesData)
                },
                success: function (response) {
                    self.loadLatestMessage();
                    if (response.error) {
                        let errorMessage = $('<div class="error-message"><div class="text"></div></div>');
                        let text = errorMessage.find('.text');
                        text.text(response.message);
                        self.chatArea.append(errorMessage);
                    } else {
                        self.chatTextarea.val('');
                    }
                    self.adjustTextareaHeight();
                    self.fileInput.val('');
                    self.uploadedFiles.text('');
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        getFiles: async function (files) {
            let filesData = [];
            const self = this;
            if (!self.validateFile(files)) {
                self.clearErrorMessage();
                let errorMessage = $('<div class="error-message"><div class="text">Invalid file type or file size exceeds the limit.</div></div>');
                self.chatArea.append(errorMessage);
                self.scrollToBottom();
                return false;
            }
            const readFileAsync = (file) => {
                return new Promise((resolve, reject) => {
                    let reader = new FileReader();
                    reader.onload = event => {
                        let fileInfo = [];
                        let base64FileData = event.target.result.split(',')[1]; // Extract the base64-encoded part
                        fileInfo.push(base64FileData);
                        fileInfo.push(file.name);
                        fileInfo.push(file.size);
                        filesData.push(fileInfo);
                        resolve();
                    };
                    reader.readAsDataURL(file);
                });
            };
        
            try {
                await Promise.all(Array.from(files).map(file => readFileAsync(file)));
                return filesData;
            } catch (error) {
                console.error(error);
            }
        },
        reopenTicket: function (ticketId, callback) {
            var self = this;

            $.ajax({
                url: this.openTicketStatusUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: self.ticketId 
                },
                success: function (response) {
                    if (response.success) {
                        self.fetchChatHeaderData(self.ticketId);
                        self.loadLatestMessage();
                        self.scrollToBottomTimeout();
                        self.isTicketClosed = false;
                        if (callback && typeof callback === 'function') {
                            callback();
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
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
            this.chatTextarea[0].style.height = '80px';

            let lineHeight = this.chatTextarea.prop('scrollHeight') / lines.length;
            let requiredHeight = (lines.length * lineHeight) - 1;

            if (requiredHeight > this.chatTextarea[0].clientHeight) {
                this.chatTextarea[0].style.height = requiredHeight + 'px';
            }
        },
        updateUploadFilesNames: function (event) {
            var selectedFiles = event.target.files;
            var uploadedFileNames = [];
            // Update uploadedFileNames with new file names
            for (var i = 0; i < selectedFiles.length; i++) {
                uploadedFileNames.push(selectedFiles[i].name);
            }

            // Update displayed file names
            this.uploadedFiles.text('');
            this.uploadedFiles.text(uploadedFileNames.join(', '));
            uploadedFileNames = []; // Clear the array for the next selection
        },
        validateFile: function (files) {
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            let maxFileSize = 15 * 1024 * 1024; // 15 MB in bytes
            var filesSize = 0;
            let filesArray = Array.from(files);

            for (let file of filesArray) {
                let fileNameParts = file.name.split('.');
                let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();
                if (!allowedExtensions.includes(fileExtension)) {
                    return false;
                }
                filesSize = filesSize + file.size;
            }

            return filesSize <= maxFileSize;
        },
        validateTextarea: function () {
            const MAX_LENGTH = 2000;
            let messageText = this.chatTextarea.val().trim();
            let errorMessage = $(`<div class="error-message"><div class="text"></div></div>`);
            let text = errorMessage.find('.text');
            let error = false;

            if (messageText === '') {
                text.text('Message cannot be empty.');
                error = true;

            }  else if (messageText.length > MAX_LENGTH) {
                text.text(`Message length cannot exceed ${MAX_LENGTH} characters.`);
                error = true;
            }

            if (error) {
                this.chatArea.append(errorMessage);
                this.scrollToBottom();
            }            

            return error;
        },
        loadLatestMessage: function () {
            var self = this;
            this.clearErrorMessage();
            $.ajax({
                url: this.messageControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: self.ticketId 
                },
                success: function (response) {
                    self.displayLatestMessage(response);
                    self.scrollToBottomTimeout();
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        displayLatestMessage: function (data) {
            let messageTemplate = this.getTicketMessageTemplate();
            var self = this;
            if (!data.sender) {
                let alertmessage = $('<div class="alert-message info"></div>');
                alertmessage.text(data.alertMessage);
                self.chatMessages.append(alertmessage);
            } else {
                messageTemplate.find('.icon .avatar img').attr('src', data.defaultImage);
                messageTemplate.find('.details .subject').text(data.subject);
                if (data.sender == 'admin') {
                    messageTemplate.find('.details .from').text("admin");
                    messageTemplate.find('.detailed-info .from').text("From: admin");
                    messageTemplate.find('.detailed-info .to').text(data.userEmail);
                } else if (data.sender == 'user') {
                    messageTemplate.find('.details .from').text(data.userEmail);
                    messageTemplate.find('.detailed-info .from').text("From: " + data.userEmail);
                    messageTemplate.find('.detailed-info .to').text('admin');
                }
                if (data.message) {
                    let messageDiv = $('<div class="message"></div>');
                    messageDiv.text(data.message);
                    messageTemplate.find('.detailed-info').append(messageDiv);
                }
                if (data.files) {
                    let messageDiv = $('<div class="files"></div>');
                    data.files.forEach(file => {
                        let fileDiv = $('<div class="file-content"></div>');
                        if (file.type == 'image') {
                            let imageLink = $('<a class="file-image" target="_blank">');
                            let imageName = $('<p class="file-name"></p>');
                            let imageTag  = $('<img class="image">');
    
                            imageLink.attr('href', file.path);
                            imageName.text(file.original_name);
                            imageTag.attr('src', file.path);
    
                            imageLink.append(imageName);
                            imageLink.append(imageTag);
    
                            fileDiv.append(imageLink);
                        }
                        if (file.type == 'file') {
                            let fileLink = $('<a class="file-name"></a>');
                            fileLink.attr('href', file.path);
                            fileLink.attr('download', file.path);
                            fileLink.text(file.original_name);
    
                            fileDiv.append(fileLink);
                        }
                        messageDiv.append(fileDiv);
                    });
                    messageTemplate.find('.detailed-info').append(messageDiv);
                }
                this.chatMessages.append(messageTemplate);
                this.scrollToBottom();
            }
        },
        getTicketMessageTemplate: function () {
            return $('<div class="ticket-message"><div class="dropdown-item"><div class="icon"><div class="avatar"><img src="" alt="User Image"></div></div><div class="details"><div class="from"></div><div class="subject"></div></div></div><div class="detailed-info"><div class="from"></div><div class="to"></div></div></div>');
        },
        scrollToBottom: function () {
            var chatMessages = this.chatMessages[0];
            chatMessages.scrollTop = chatMessages.scrollHeight - chatMessages.clientHeight;
        },
        scrollToBottomTimeout: function () {
            var chatMessages = this.chatMessages[0];
            setTimeout(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight - chatMessages.clientHeight;
            }, 30);
        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        },
    });
});
