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
            this.ticketId = null;
            this.displayWelcomeMessage();
            this.ticketControllerUrl = config.ticketControllerUrl;
            this.messageControllerUrl = config.messageControllerUrl;
            this.addAdminMessageControllerUrl = config.addAdminMessageControllerUrl;
            this.addFileMessageControllerUrl = config.addFileMessageControllerUrl;
            this.changeTicketStatusControllerUrl = config.changeTicketStatusControllerUrl;
        },

        setupElements: function () {
            this.userList = $('#ticket-admin-container .user-list');
            this.totalChats = $('#ticket-admin-container .total-chats');
            this.chatArea = $('#ticket-admin-container .chat-area');
            this.chatMessages = $('#ticket-admin-container .chat-messages');
            this.chatTextarea = $('#ticket-admin-container .chat-input textarea');
            this.welcomeMessage = $('#ticket-admin-container .welcome-message');
            this.fileInput = $('#ticket-admin-container #file-input');
            this.statusSelect = $('#ticket-status');
            this.statusModal = $('#status-update-modal');
        },

        attachEventHandlers: function () {
            var self = this;

            this.userList.on('click', '.user-item', $.proxy(this.handleUserItemClick, this));
            $('.chat-input button').on('click', $.proxy(this.handleSendMessage, this));

            this.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    self.handleSendMessage(self);
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
            var previousSelectedOption;

            this.statusSelect.on('focus', function () {
                previousSelectedOption = self.statusSelect.val();
            }).change(function() {
                self.handleTicketStatusUpdate(previousSelectedOption);
            });
        },
        handleUserItemClick: function (event) {
            this.welcomeMessage.hide();
            this.chatArea.show();
            $('.user-item').removeClass('selected');
            let ticketItem = $(event.currentTarget);
            ticketItem.addClass('selected');
            ticketItem.find('.latest-message').addClass('opened');
            this.ticketId = parseInt(ticketItem.attr('data-ticket-id'));
            this.fetchChatHeaderData(this.ticketId);
            this.displayMessages(this.ticketId);
        },
         // Function to fetch data via AJAX and update chat-header
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
        handleTicketStatusUpdate: function (previousSelectedOption) {
            var self = this;
            this.statusModal.show();
            this.statusModal.find('#modalYesBtn').on('click', function () {
                var statusValue = self.statusSelect.val();
                $.ajax({
                    url: self.changeTicketStatusControllerUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: { 
                        ticket_id: self.ticketId,
                        status_value: statusValue
                    },
                    success: function (response) {
                        self.statusModal.hide();
                        self.fetchChatHeaderData(self.ticketId);
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            });
            this.statusModal.find('#modalNoBtn').on('click', function () {
                self.statusSelect.val(previousSelectedOption);
                self.statusModal.hide();
            });
        },
        updateChatHeader: function (data) {
            var customerNameElement = $('.ticket-customer .customer');
            var orderLinkElement = $('.ticket-type');
            var subjectElement = $('.ticket-subject .subject');
            var createdAtElement = $('.ticket-date .date');
            var statusElement = $('.ticket-status');
            var statusElementLabel = $('.ticket-status .status');

            customerNameElement.text(data.customerName);
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
            if (data.isTicketOpened) {
                statusDiv.addClass('active');
            } else if (data.isTicketPending) {
                statusDiv.addClass('pending');
            } else if (data.isTicketClosed) {
                statusDiv.addClass('closed');
            }
            statusElement.append(statusDiv);
        },
        handleSendMessage: function (event) {
            this.clearErrorMessage();
            let messageText = this.chatTextarea.val().trim();
            messageText = messageText.replace(/\n/g, '<br>');

            var self = this;

            if (this.validateTextarea()) {
                return;
            }
            
            $.ajax({
                url: this.addAdminMessageControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: this.ticketId,
                    message: messageText
                },
                success: function (response) {
                    self.displayMessages();
                    self.chatTextarea.val('');
                    self.adjustTextareaHeight();
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
        adjustLatestMessage: function () {
            let selectedTicket = this.ticketId;
            let latestMessage = selectedTicket.messages[selectedTicket.messages.length - 1];
            let userItem = $(`.user-item[data-ticket-id="${this.ticketId}"]`);

            if (latestMessage.sender === 'user') {
                userItem.find('.latest-message').text(latestMessage.text);
            } else {
                userItem.find('.latest-message').text('Sent');
            }
        },
        handleFileAttachment: function () {
            this.clearErrorMessage();
            var self = this;

            let file = this.fileInput[0].files[0];

            if (!this.validateFile(file)) {
                let errorMessage = $('<div class="error-message"><div class="text">Invalid file type or file size exceeds the limit.</div></div>');
                this.chatArea.append(errorMessage);
                this.scrollToBottom();
                this.fileInput.val('');
                return;
            }

            let reader = new FileReader();
            reader.onload = (event) => {
                let base64FileData = event.target.result.split(',')[1]; // Extract the base64-encoded part
                
                $.ajax({
                    url: this.addFileMessageControllerUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        ticket_id: self.ticketId,
                        data: base64FileData,
                        fileName: file.name
                    },
                    success: function (response) {
                        self.displayMessages();
                        self.chatTextarea.val('');
                        self.adjustTextareaHeight();
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            };
            reader.readAsDataURL(file);
        },
        validateFile: function (file) {
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            let maxFileSize = 3 * 1024 * 1024; // 3 MB in bytes
            let fileNameParts = file.name.split('.');
            let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();

            return allowedExtensions.includes(fileExtension) && file.size <= maxFileSize;
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
                this.scrollToBottom();
                this.chatArea.append(errorMessage);
            }            

            return error;
        },
        displayMessages: function () {
            var self = this;
            this.chatMessages.empty();
            this.clearErrorMessage();
            
            $.ajax({
                url: this.messageControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: this.ticketId 
                },
                success: function (response) {
                    response.forEach(element => {
                        let messageDiv = $('<div></div>').addClass(`message-chat ${element.sender}`);
        
                        if (element.type === 'file') {
                            let fileDiv = self.handleMessageAttachments(element.originalName, element.attachmentPath);
                            messageDiv.append(fileDiv);
                        } else {
                            messageDiv.html(element.message);
                        }
                        self.chatMessages.append(messageDiv);
                    });
                    self.scrollToBottom();
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        handleMessageAttachments: function (originalName, path) {
            let fileType = this.getFileType(originalName);
            let fileContent = $('<div class="file-content"></div>');

            if (fileType === 'image') {
                let fileImageContent = $('<a><p class="file-name"></p><img class="file-image"></a>');
                let image = fileImageContent.find('.file-image');
                let fileName = fileImageContent.find('.file-name');

                fileImageContent.attr('href', path);
                fileImageContent.attr('target', '_blank');
                image.attr('src', path);
                fileName.text(originalName);
                fileContent.append(fileImageContent);
              
            } else {
                let fileName = $('<a class="file-name"></a>');
    
                fileName.text(originalName);
                fileName.attr('href', path);
                fileName.attr('download', path);
                fileContent.append(fileName);
            }
            return fileContent;
        },
        getFileType: function (originalName) {
            // Get the file extension by splitting the original name
            const fileExtension = originalName.split('.').pop().toLowerCase();
        
            // Define arrays of common image and document file extensions
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        
            // Check if the file extension is in the imageExtensions array
            if (imageExtensions.includes(fileExtension)) {
                return 'image';
            }

            return 'file';
        },
        displayWelcomeMessage: function () {
            this.chatArea.hide();
            this.welcomeMessage.show();
            this.welcomeMessage.text($t('Select a chat and start messaging now...'));
        },
        scrollToBottom: function () {
            var chatMessages = this.chatMessages[0];
            chatMessages.scrollTop = chatMessages.scrollHeight - chatMessages.clientHeight;
        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        },
        getSelectedTicket: function () {
            return this.tickets.find(user => user.id === this.ticketId);
        }
    });
});
