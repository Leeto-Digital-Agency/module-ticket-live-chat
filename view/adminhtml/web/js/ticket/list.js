define([
    'jquery',
    'uiComponent',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            this._super();
            this.setupElements();
            this.attachEventHandlers();
            this.ticketId = null;
            this.statusGroup.find('.status.first').trigger('click');
            this.displayWelcomeMessage();
            this.ticketControllerUrl = config.ticketControllerUrl;
            this.messageControllerUrl = config.messageControllerUrl;
            this.addAdminMessageControllerUrl = config.addAdminMessageControllerUrl;
            this.changeTicketStatusControllerUrl = config.changeTicketStatusControllerUrl;
            this.showTicketsByStatusUrl = config.showTicketsByStatusUrl;
            this.allowedExtensions = config.allowedExtensions;
            this.maxFilesSize = config.maxFilesSize;
            this.maxFilesToUpload = config.maxFilesToUpload;
            this.adminAvatarImage = config.adminAvatarImage;
            this.activeTicketStatus = null;
            this.isLatestMessageFromUser = null;
            this.currentTicketStatusId = null;
        },

        setupElements: function () {
            this.userList = $('#ticket-admin-container .user-list');
            this.chatArea = $('#ticket-admin-container .chat-area');
            this.chatMessages = $('#ticket-admin-container .chat-messages');
            this.chatTextarea = $('#ticket-admin-container .chat-input textarea');
            this.welcomeMessage = $('#ticket-admin-container .welcome-message');
            this.fileInput = $('#ticket-admin-container #file-input');
            this.statusSelect = $('#ticket-admin-container #ticket-status-list');
            this.statusModal = $('#status-update-modal');
            this.uploadedFiles = $('#uploaded-files-list');
            this.ticketMessage = $('#ticket-admin-container');
            this.loadingDivChatArea = $('#ticket-admin-container .chat-area .loading-container');
            this.loadingDivUserList = $('#ticket-admin-container .user-list-wrapper .loading-container');
            this.statusGroup = $('#ticket-admin-container .ticket-status-grouped');
            this.chatsInfo = $('#ticket-admin-container .chats-info');
            this.refreshButton = $('.refresh-button button');
        },

        attachEventHandlers: function () {
            var self = this;

            this.userList.on('click', '.user-item', $.proxy(this.handleUserItemClick, this));
            $('.chat-input button').on('click', async function () {
                let isLatestFromUser = await self.getIsLatestMessageFromUser();
                if (isLatestFromUser) {
                    self.chatTextarea.attr('disabled', true);
                    self.fileInput.attr('disabled', true);
                    $('.chat-input button').addClass('disabled');
                    $('.chat-input button').text('Sending...');
                    self.handleSendMessage(function () {
                        self.chatTextarea.attr('disabled', false);
                        self.fileInput.attr('disabled', false);
                        $('.chat-input button').removeClass('disabled');
                        $('.chat-input button').text('Send');
                    });
                } else {
                    self.handleSendMessage();
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
            var previousSelectedOption;

            this.statusSelect.on('focus', function () {
                previousSelectedOption = $(this).val();
            }).change(function() {
                self.handleTicketStatusUpdate(previousSelectedOption, $(this));
            });

            this.statusGroup.find('.status').on('click', function (event) {
                self.loadTicketsByStatus(event);
            });

            this.refreshButton.on('click', function() {
                self.loadingDivChatArea.css('display', 'flex');
                let ticketId = parseInt(self.refreshButton.attr('data-ticket-id'));
                let oldStatusId = parseInt(self.currentTicketStatusId);
                self.fetchChatHeaderData(ticketId, function () {
                    if (oldStatusId != self.currentTicketStatusId) {
                        self.statusGroup.find('[data-status-id="' + self.currentTicketStatusId + '"]').trigger('click');
                    }
                });
                self.loadMessages(function () {
                    self.loadingDivChatArea.hide();
                });
            });
        },
        getIsLatestMessageFromUser: async function () {
            let self = this;
            let response;
            response = await $.ajax({
                url: self.messageControllerUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    ticket_id: self.ticketId,
                    latest_user: true
                },
            });

            return response.isLatestMessageFromUser;
        },
        handleUserItemClick: async function (event) {
            var self = this;
            this.loadingDivChatArea.css('display', 'flex');
            this.welcomeMessage.hide();
            this.chatArea.show();
            $('.user-item').removeClass('selected');
            let ticketItem = $(event.currentTarget);
            ticketItem.addClass('selected');
            ticketItem.find('.latest-message').addClass('opened');
            this.ticketId = parseInt(ticketItem.attr('data-ticket-id'));
            this.refreshButton.attr('data-ticket-id', this.ticketId);
            this.fetchChatHeaderData(this.ticketId);
            this.loadMessages(function () {
                self.loadingDivChatArea.hide();
            });
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
        loadTicketsByStatus: function (event) {
            this.statusGroup.find('.active').removeClass('active');
            $(event.currentTarget).addClass('active');
            var statusId = $(event.currentTarget).attr('data-status-id');
            this.activeTicketStatus = statusId;
            this.loadingDivUserList.css('display', 'flex');
            this.displayTicketsByStatus(statusId);
        },
        displayTicketsByStatus: function (statusId) {
            var self = this;
            $.ajax({
                url: self.showTicketsByStatusUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    status_id: statusId 
                },
                success: function (response) {
                    self.userList.empty();
                    self.updateTicketsList(response, function () {
                        self.loadingDivUserList.hide();
                    });
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        updateTicketsList: function (data, callback) {
            var self = this;
            this.chatsInfo.find('.tickets-count').text(data.totalTickets + ' Tickets');
            data.data.forEach(element => {
                let ticketListTemplate = self.getTicketListTemplate();
                ticketListTemplate.find('.user-avatar img').attr('src', element.imageSrc);
                let tikcetUserDiv = $('<h3></h3>');
                tikcetUserDiv.text(element.username);
                ticketListTemplate.find('.user-details').append(tikcetUserDiv);
                let lastMessageStatus = $('<p class="latest-message"><span></span></p>');
                if (element.latestMessage.isAdmin == 1) {
                    lastMessageStatus.find('span').text('Sent');
                    lastMessageStatus.addClass('from-admin');
                } else {
                    let latestMessageToShow = element.latestMessage.latest_message.replace(/<br>/g, ' ');
                    lastMessageStatus.find('span').text(latestMessageToShow);
                    lastMessageStatus.addClass('from-customer');
                }
                if (parseInt(element.ticketId) == self.ticketId) {
                    ticketListTemplate.addClass('selected');
                    lastMessageStatus.addClass('opened');
                }
                ticketListTemplate.attr('data-ticket-id', element.ticketId);

                ticketListTemplate.find('.user-details').append(lastMessageStatus);
                self.userList.append(ticketListTemplate);
            });
            if (callback && typeof callback === 'function') {
                callback();
            }
        },
        fetchChatHeaderData: function (ticketId, callback) {
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
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        handleTicketStatusUpdate: function (previousSelectedOption, target) {
            var self = this;
            this.statusModal.show();

            this.statusModal.find('#modalYesBtn').off('click');
            this.statusModal.find('#modalNoBtn').off('click');
            this.statusModal.find('#modalYesBtn').on('click', function () {
                var statusValue = target.val();
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
                        self.loadMessages();
                        self.statusGroup.find('[data-status-id="' + statusValue + '"]').trigger('click');
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            });
            this.statusModal.find('#modalNoBtn').on('click', function () {
                target.val(previousSelectedOption);
                self.statusModal.hide();
            });
        },
        updateChatHeader: function (data) {
            var customerNameElement = $('.ticket-customer .customer');
            var orderLinkElement = $('.ticket-type');
            var subjectElement = $('.ticket-subject .subject');
            var createdAtElement = $('.ticket-date .date');
            var statusElement = $('.ticket-status #status-update');
            var self = this;

            customerNameElement.text(data.customerName);
            statusElement.find("option").prop('selected', false); 
            statusElement.find("[value='" + data.statusId + "']").prop('selected', true);
            this.currentTicketStatusId = data.statusId;
            statusElement.removeAttr('class');
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
            if (data.isTicketClosed) {
                statusElement.addClass('closed');
                self.chatTextarea.attr('disabled', true);
                self.chatTextarea.attr('placeholder', 'This ticket is closed.');
                self.fileInput.attr('disabled', true);
                $('.chat-input button').addClass('disabled');
            } else {
                self.chatTextarea.attr('disabled', false);
                self.chatTextarea.attr('placeholder', 'Type your message...');
                self.fileInput.attr('disabled', false);
                $('.chat-input button').removeClass('disabled');
                if (data.isTicketOpened) {
                    statusElement.addClass('active');
                } else if (data.isTicketPending) {
                    statusElement.addClass('pending');
                }
            }
        },
        handleSendMessage: async function (callback) {
            this.clearErrorMessage();
            let messageText = this.chatTextarea.val().trim();
            let files = this.fileInput[0].files;
            messageText = messageText.replace(/\n/g, '<br>');
            var self = this;
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
                    ticket_id: this.ticketId,
                    message: messageText,
                    files_data: JSON.stringify(filesData)
                },
                success: function (response) {
                    self.adjustLatestMessage();
                    self.loadMessages();
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
                    self.updateTicketImage();
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        updateTicketImage: function () {
            let selectedTicket = $(".user-item.selected");
            selectedTicket.find('.user-avatar img').attr('src', this.adminAvatarImage);
        },
        getFiles: async function (files) {
            let filesData = [];
            const self = this;
            if (!self.validateFile(files)) {
                self.clearErrorMessage();
                let errorMessage = $('<div class="error-message"><div class="text">Invalid file type or file size exceeds the limit.</div></div>');
                self.chatArea.append(errorMessage);
                self.scrollToBottom();
                return;
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
        adjustLatestMessage: function () {
            var latestMessage = this.userList.find('.user-item.selected .latest-message');
            latestMessage.text('Sent');
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
        validateFile: function (files) {
            var self = this;
            let convertedMaxFilesSize = this.maxFilesSize * 1024 * 1024; // 15 MB in bytes
            var filesSize = 0;
            let filesArray = Array.from(files);
            if (filesArray.length > this.maxFilesToUpload) {
                return false;
            }
            for (let file of filesArray) {
                let fileNameParts = file.name.split('.');
                let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();
                if (!self.allowedExtensions.includes(fileExtension)) {
                    return false;
                }
                filesSize = filesSize + file.size;
            }

            return filesSize <= convertedMaxFilesSize;
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
        loadMessages: function (callback) {
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
                    self.displayMessages(response);
                    self.scrollToBottomTimeout();
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        displayMessages: function (data) {
            var self = this;
            data.forEach(element => {
                if (element.isAlert && parseInt(element.isAlert)) {
                    let alertmessage = $('<div class="alert-message info"></div>');
                    alertmessage.text(element.alertMessage);
                    self.chatMessages.append(alertmessage);
                } else {
                    let messageTemplate = self.getTicketMessageTemplate();
                    messageTemplate.find('.icon .avatar img').attr('src', element.imageSrc);
                    messageTemplate.find('.details .subject').text(element.subject);
                    if (element.sender == 'admin') {
                        messageTemplate.find('.details .from').text("admin");
                        messageTemplate.find('.detailed-info .from').text("From: admin");
                        messageTemplate.find('.detailed-info .to').text('To: ' + element.userEmail);
                    } else if (element.sender == 'user') {
                        messageTemplate.find('.details .from').text(element.userEmail);
                        messageTemplate.find('.detailed-info .from').text("From: " + element.userEmail);
                        messageTemplate.find('.detailed-info .to').text('To: admin');
                    }
                    if (element.message) {
                        let messageDiv = $('<div class="chat-message"></div>');
                        messageDiv.html(element.message);
                        messageTemplate.find('.detailed-info').append(messageDiv);
                    }
                    if (element.files) {
                        let messageDiv = $('<div class="files"></div>');
                        element.files.forEach(file => {
                            let fileDiv = $('<div class="file-content"></div>');
                            if (file.type == 'image') {
                                let imageLink = $('<a class="file-image" target="_blank">');
                                let imageName = $('<p class="file-name"></p>');
                                let imageTag  = $('<img class="message-image">');
    
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
                    self.chatMessages.append(messageTemplate);
                }
            });
            self.scrollToBottom();
        },
        getTicketMessageTemplate: function () {
            return $('<div class="ticket-message"><div class="dropdown-item"><div class="icon"><div class="avatar"><img src="" alt="User Image"></div></div><div class="details"><div class="from"></div><div class="subject"></div></div></div><div class="detailed-info"><div class="from"></div><div class="to"></div></div></div>');
        },
        getTicketListTemplate: function () {
            return $('<div class="user-item" data-ticket-id=""><div class="user-avatar"><img src="" alt="Image Placeholder"></div><div class="user-details"></div></div>');
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
        scrollToBottomTimeout: function () {
            var chatMessages = this.chatMessages[0];
            setTimeout(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight - chatMessages.clientHeight;
            }, 30);
        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        }
    });
});
