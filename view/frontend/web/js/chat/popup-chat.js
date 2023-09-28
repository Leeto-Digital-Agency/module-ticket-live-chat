define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/cookies'
], function ($, Component, $t, customerData, url) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            this.setupElements(config);
            this.setupWebSocket();
            this.attachEventHandlers();
        },
        setupElements: async function (config) {
            this.conn = new WebSocket(`ws://${config.webBaseUrl}:${config.webSocketPort}`);
            this.chatButton = $('#chat-container .chat-button').first();
            this.chatPopup = $('#chat-container .chat-popup').first();
            this.chatMessages = $('#chat-container .chat-messages').first();
            this.chatTextarea = $('#chat-container .chat-input textarea').first();
            this.chatInput = $('#chat-container .chat-input').first();
            this.chatWrapper = $('#chat-container .chat-wrapper');
            this.attachIcon = $('#chat-container .attach-icon').first();
            this.fileInput = $('#chat-container #file-input').first();
            this.emailInputSection = $('#chat-container .email-input-section').first();
            this.adminStatusSection = $('#chat-container .chat-popup .admin-status');
            this.ticketSection = $('#chat-container .ticket-section');
            this.submitTicketButton = this.ticketSection.find('#submit-ticket-button');
            this.newChatSection = this.ticketSection.find('.new-chat');
            this.ongoingChatSection = this.ticketSection.find('.ongoing-chat');
            this.thankYouSection = this.ticketSection.find('.thank-you-wrapper');
            this.chatClosedSection = $('#chat-container .chat-closed-section');
            this.startNewChatButton = $('#chat-container #start-new-chat');
            this.ticketSection.hide();
            this.thankYouSection.hide();
            this.isChatConvertedToTicket = false;
            this.adminStatus = '';
            this.storedChatMessages = [];
            this.email = $.cookie('guest_email');
            this.uuid = $.cookie('guest_uuid');
            this.userId = config.loggedInUserId;
            this.supportAvatarImagePath = config.supportAvatarImagePath;
            this.countUnreadMessages = 0;
            this.newAdminMessage = false; 
            this.supportDeafultFirstMessageData = {
                newMessage: true,
                fromId: null,
                isAdmin: true,
                email: null,
                message: $t('Hi! How can we assist you?'),
                type: "text",
            }
            this.addSupportMessageDOM(this.supportDeafultFirstMessageData);
            
            this.conn.onmessage = async (event) => {
                let data = JSON.parse(event.data);

                if (this.isChatConvertedToTicket) {
                    if (data.adminStatus) {
                        this.updateAdminStatus(data.adminStatus);
                    }
                    return;
                }
                if (data.errorMessages) {
                    this.handleErrorMessage(data.errorMessages);
                    return;
                }
                if (data.adminStatus) {
                    this.updateAdminStatus(data.adminStatus);
                    if (this.adminStatus === 'offline') {
                        this.ticketSection.show();
                        this.emailInputSection.hide();
                        this.chatWrapper.hide(); // hide chat messages
                        this.chatInput.hide(); // hide chat textarea
                        let chatId = await this.getChatIdFromDb();

                        if (chatId) {
                            this.newChatSection.hide();
                            this.ongoingChatSection.show();
                        } else {
                            this.newChatSection.show();
                            this.ongoingChatSection.hide();
                        }
                        return;
                    } else if (this.adminStatus === 'online') {
                        this.ticketSection.hide();
                        if (!this.userId && !this.uuid) {
                            this.emailInputSection.show();
                        } else {
                            this.emailInputSection.hide();
                            this.chatWrapper.show(); // show chat messages
                            this.chatInput.show(); // show chat textarea
                        }
                    }
                } else if (data.chatClosed && data.byAdmin) {
                    this.handleClosedChatByAdmin(data);
                    return;
                }else {
                    this.newAdminMessage = true;
                    this.incrementUnreadMessagesCount(true);
                    this.markAsRead();
                    this.addSupportMessageDOM(data);
                    this.scrollToBottom();
                }
            };

            // Prepare data for server notification
            let chatId = await this.getChatIdFromDb();
            const notificationData = {
                newConnection: true,
                role: "user",
                email: this.email,
                chatId: chatId,
                uuid: this.uuid
            };
            // Function to send data when the connection is ready
            const sendDataWhenReady = () => {
                return new Promise((resolve, reject) => {
                    if (this.conn.readyState === WebSocket.OPEN) {
                        this.conn.send(JSON.stringify(notificationData));
                        resolve();
                    } else {
                        this.conn.addEventListener('open', () => {
                            this.conn.send(JSON.stringify(notificationData));
                            resolve();
                        });
                    }
                });
            };
            // Send data when the connection is ready
            await sendDataWhenReady();

            if (!this.userId && !this.uuid) {
                this.chatWrapper.hide(); // hide chat messages
                this.chatInput.hide(); // hide chat textarea
            } else {
                this.emailInputSection.hide();
                let response = await this.getChatMessages();
                this.storedChatMessages =  response.messages;
                this.countUnreadMessages = response.unreadMessagesCount;
                this.incrementUnreadMessagesCount();
                this.loadChatMessages();
            }
        },
        setupWebSocket: function () {
            this.conn.onopen = () => {
                console.log('WebSocket connection established.');
            };
        
            this.conn.onclose = (event) => {
                this.updateAdminStatus('offline');
                this.ticketSection.show();
                this.newChatSection.show();
                this.ongoingChatSection.hide();
                this.emailInputSection.hide();
                this.chatWrapper.hide();
                this.chatInput.hide();
                console.log('WebSocket connection closed:', event.reason);
            };
        
            this.conn.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        },
        attachEventHandlers: function () {
            var self = this;

            self.chatButton.on('click', function () {
                self.toggleChatPopup();
            });

            $('#chat-container .close-button').on('click', function () {
                self.closeChatPopup();
            });

            $('#chat-container .send-icon').on('click', function () {
                self.sendMessage();
            });

            $('#continue-button').on('click', async function (e) {
                e.preventDefault();
                if (!self.validateEmail()) {
                    return;
                }
                self.email = $('#email-input').val();
                if (!$.cookie('guest_uuid')) {
                    self.uuid = self.generateUUID();
                    $.cookie('guest_uuid', self.uuid, { expires: 1 });
                    $.cookie('guest_email', self.email, { expires: 1 });
                }

                let response = await self.getChatMessages();
                if (response && response.isEmailTaken) {
                    self.clearGuestInfo();
                    self.email = null;
                    self.uuid = null;
                    $("#email-error-message").show();
                    $("#email-error-message").text('Email is already taken.');
                    return;

                } else {
                    self.storedChatMessages =  response.messages;
                }
                self.showGuestChatSection();
                self.loadChatMessages();
            });

            self.submitTicketButton.on('click', async function (e) {
                e.preventDefault();
                let ticketStatus = parseInt($('input[name="ticket-status-choice"]:checked').val());
                let ticketErrorMessage = self.ongoingChatSection.find('#ticket-error-message');
                
                if (ticketStatus) {
                    ticketErrorMessage.hide();
                    let chatId = await self.getChatIdFromDb();
                    $.ajax({
                        url: url.build('support/chat/createticket'),
                        method: "GET",
                        dataType: "json",
                        data: {
                            chatId: chatId,
                            ticketStatus: ticketStatus
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                self.isChatConvertedToTicket = true;
                                self.clearGuestInfo();
                                self.ongoingChatSection.hide();
                                self.newChatSection.hide();
                                ticketErrorMessage.hide();
                                self.chatWrapper.hide();
                                self.chatInput.hide();
                                let ticketLink = self.thankYouSection.find('.ticket-link');
                                
                                if (ticketStatus === 1) {
                                    self.thankYouSection.find('.thank-you-message')
                                        .text($t("Thank you for contacting us! You'll be notified via email when we respond to your ticket."));
                                } else {
                                    self.thankYouSection.find('.thank-you-message')
                                        .text($t('Thank you for contacting us! Your ticket is set to closed.'));
                                }
                                ticketLink
                                    .attr('href', response.ticketUrl)  
                                    .text('Click here to view your ticket.');
                                
                                ticketLink.show();
                                self.thankYouSection.show();
                            } else {
                                ticketErrorMessage.show();
                                ticketErrorMessage.text(response.errorMessage);
                            }
                        }
                    });
                } else {
                    ticketErrorMessage.show();
                    ticketErrorMessage.text('Please select an option.');
                }
            });

            self.startNewChatButton.on('click', function (e) {
                e.preventDefault();
                self.handleStartNewChat();
            });

            self.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    self.sendMessage();
                } else if (event.key === 'Enter' && event.shiftKey) {
                    event.preventDefault();
                    self.handleTextareaShiftEnter(event);
                }
            });

            self.chatTextarea.on('input', function () {
                self.adjustTextareaHeight();
            });

            self.fileInput.on('change', function () {
                self.handleFileAttachment();
                self.scrollToBottom();
            });
        },
        toggleChatPopup: function () {
            this.chatPopup.toggleClass('active');
            this.chatButton.toggleClass('active');
            this.markAsRead();
            
        },
        showGuestChatSection: function () {
            this.emailInputSection.hide(); // Hide email input form
            this.chatWrapper.show(); // show chat messages
            this.chatInput.show(); // show chat textarea
            this.adminStatusSection.show(); // show email status
        },
        getChatMessages: async function () {
            // Load chat messages from server
            let response;

            try {
                response = await $.ajax({
                    url: url.build('support/chat/getmessages'),
                    method: "GET",
                    dataType: "json",
                    data: {
                        email: this.email,
                        userId: this.userId,
                        uuid: this.uuid
                    }
                });

            } catch (error) {
                console.log(error);
                return;
            }
            console.log(response);
            return response;
        },
        loadChatMessages: function () {
            if (!this.storedChatMessages || this.storedChatMessages.length === 0) {
                return;
            }
            this.storedChatMessages.forEach(message => {
                if (message.sender === 'support') {
                    message.message = message.text;
                    this.addSupportMessageDOM(message);
                    
                } else {
                    let messageDiv = $('<div></div>')
                    .addClass(`message user-message`)
                    .append($('<div></div>').addClass('text'));
    
                    if (message.type === 'file') {
                        messageDiv.append(this.renderFile(message.originalName, message.path));
    
                    } else {
                        messageDiv.find('.text').text(message.text.replace(/\n/g, '<br>'));
                    }
    
                    this.chatMessages.append(messageDiv);
                }

            });
            this.scrollToBottom();
        },
        closeChatPopup: function () {
            this.chatPopup.removeClass('active');
        },
        sendMessage: function () {
            this.clearErrorMessage();
            let messageText = this.chatTextarea.val();
            if (!this.validateText(messageText)) {
                return;
            }
            let message = $('<div class="message user-message"><div class="text"></div></div>');
            message.find('.text').html(messageText.replace(/\n/g, '<br>'));
            
            // Send message to server
            let data = {
                newMessage: true,
                fromId: this.userId,
                isAdmin: false,
                email: this.email,
                message: messageText,
                type: "text",
                uuid: this.uuid
            }
            this.conn.send(JSON.stringify(data));
            
            this.chatMessages.append(message);
            this.scrollToBottom();
            this.chatTextarea.val('');
            this.adjustTextareaHeight();
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

            let fileMessage = this.createFileMessage(file);

            // Read the file and convert it to base64
            let reader = new FileReader();
            reader.onload = (event) => {
                let base64FileData = event.target.result.split(',')[1]; // Extract the base64-encoded part
                let data = {
                    newMessage: true,
                    fromId: this.userId ?? null,
                    isAdmin: false,
                    email: this.email,
                    message: null,
                    type: "file",
                    uuid: this.uuid,
                    attachment: {
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        data: base64FileData
                    }
                };
                this.conn.send(JSON.stringify(data));
            };
            reader.readAsDataURL(file);
            
            this.chatMessages.append(fileMessage);
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
        renderFile: function (originalName, filePath) {
            let fileType = this.getFileType(originalName);
            let fileContent = $('<div class="file-content"></div>');

            if (fileType == 'image') {
                let fileImageContent = $('<a><p class="file-name"></p><img class="file-image"></a>');
                let image = fileImageContent.find('.file-image');
                let fileNameElement = fileImageContent.find('.file-name');

                fileImageContent.attr('href', filePath);
                fileImageContent.attr('target', '_blank');
                image.attr('src', filePath);
                fileNameElement.text(originalName);
                fileContent.append(fileImageContent);
            } else {
                let fileNameElement = $('<a class="file-name"></a>');

                fileNameElement.text(originalName);
                fileNameElement.attr('href', filePath);
                fileNameElement.attr('download', originalName);
                fileContent.append(fileNameElement);
            }

            return fileContent;
        },
        validateFile: function (file) {
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            let maxFileSize = 3 * 1024 * 1024; // 3 MB in bytes
            let fileNameParts = file.name.split('.');
            let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();

            return allowedExtensions.includes(fileExtension) && file.size <= maxFileSize;
        },
        validateText: function (text) {
            const MAX_MESSAGE_LENGTH = 2000;
            let isValid = true;

            let errorMessageSection = $('<div class="error-message"></div>');
            let textMessage = '';
            if (text.length > MAX_MESSAGE_LENGTH) {
                textMessage = $('<div class="text"></div>')
                    .text($t(`Message length must not exceed ${MAX_MESSAGE_LENGTH} characters.`));
                isValid = false;
            }
            if (text.trim() === '') {
                textMessage = $('<div class="text"></div>')
                    .text($t('Message is required.'));
                isValid = false;
            }
            if (!isValid) {
                errorMessageSection.append(textMessage);
                this.chatMessages.append(errorMessageSection);
                this.scrollToBottom();
            }
            return isValid;
        },
        getFileType: function (originalName) {
            const fileExtension = originalName.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

            if (imageExtensions.includes(fileExtension)) {
                return 'image';
            }

            return 'file';
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
        addSupportMessageDOM: function (data) {
            let messageHeader = $('<div></div>')
                .addClass('support-message-header')
                .append($('<img></img>').addClass('support-message-avatar')
                    .attr('src', this.supportAvatarImagePath))
                .append($('<span></span>').addClass('support-message-name').text('Support'));

            // Create the message content
            let messageContent = $('<div></div>').addClass('text')

            // Create the main message container
            let messageDiv = $('<div></div>')
                .addClass('message support-message')
                .append(messageHeader)
                .append($('<div></div>').addClass('support-message-content').append(messageContent));
            
            if (data.type === 'file') {
                messageDiv.append(this.renderFile(data.originalName, data.path));

            } else {
                messageDiv.find('.text').text(data.message.replace(/\n/g, '<br>'));
            }

            this.chatMessages.append(messageDiv);
        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        },

        validatePattern: function (email) {
            let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailPattern.test(email);
        },
        scrollToBottom: function () {
            setTimeout(() => {
                this.chatWrapper[0].scrollTop = this.chatMessages.prop('scrollHeight');
            }, 30);
        },
        getChatId: function () {
            if (this.storedChatMessages && this.storedChatMessages.length > 0) {
                return this.storedChatMessages[0].chatId;
            }
            return null;
        },
        getChatIdFromDb: async function () {
            let response;

            try {
                response = await $.ajax({
                    url: url.build('support/chat/getchatid'),
                    method: "GET",
                    dataType: "json",
                    data: {
                        email: this.email,
                        userId: this.userId,
                        uuid: this.uuid
                    }
                });

            } catch (error) {
                console.log(error);
                return null;
            }
            return response.chatId;
        },
        updateAdminStatus: function (status) {
            this.adminStatus = status;
            let dot = this.adminStatusSection.find('.dot');
            let adminName = this.adminStatusSection.find('.admin-name');

            if (this.adminStatus === 'online') {
                dot.removeClass('offline');
                dot.addClass('online');
                adminName.text('Support is online');

            } else if (this.adminStatus === 'offline') {
                dot.removeClass('online');
                dot.addClass('offline');
                adminName.text('Support is offline');
            }
        },

        generateUUID: function () {
            var timestamp = new Date().getTime();
            if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
                timestamp += performance.now(); // Use high-precision timer if available
            }
            
            var uuidPattern = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx';
            
            return uuidPattern.replace(/[xy]/g, function (char) {
                var randomValue = (timestamp + Math.random() * 16) % 16 | 0;
                timestamp = Math.floor(timestamp / 16);
                return (char === 'x' ? randomValue : (randomValue & 0x3 | 0x8)).toString(16);
            });
        },
        incrementUnreadMessagesCount: function (newMessage = false) {
            // check if user has opened the chat popup
            if (this.chatPopup && !this.chatPopup.hasClass('active')) {
                if (newMessage) {
                    this.countUnreadMessages++;
                }
                if (this.countUnreadMessages > 0) {
                    this.chatButton.find('.unread-messages-count').text(this.countUnreadMessages);
                    this.chatButton.find('.unread-messages-count').show();
                }
            }
        },
        markAsRead: async function () {
            if (!this.chatPopup || this.chatPopup && !this.chatPopup.hasClass('active')) {
                return;
            }
            if (this.newAdminMessage || this.countUnreadMessages > 0) {
                if (this.countUnreadMessages > 0) {
                    this.countUnreadMessages = 0;
                    this.chatButton.find('.unread-messages-count').hide();
                }
                if (this.newAdminMessage) {
                    this.newAdminMessage = false;
                }

                let chatId = await this.getChatIdFromDb();
                $.ajax({
                    url: url.build('support/chat/markasread'),
                    method: "GET",
                    dataType: "json",
                    data: {
                        chatId: chatId,
                    },
                    success: function (response) {
                        console.log(response);
                    }
                });
            }
        },
        clearGuestInfo: function () {
            $.removeCookie('guest_uuid', { path: '/' });
            $.removeCookie('guest_email', { path: '/' });
        },
        refreshGuestInfo: function () {
            this.clearGuestInfo();
            this.uuid = this.generateUUID();
            $.cookie('guest_uuid', this.uuid, { expires: 1 });
            $.cookie('guest_email', this.email, { expires: 1 });
        },
        handleClosedChatByAdmin: function (data) {
            this.isChatConvertedToTicket = true;
            this.refreshChatData();
            this.chatClosedSection.find('.ticket-link')
                .attr('href', data.ticketUrl)
                .text($t('Click here to view your ticket.'));
            this.chatClosedSection.show();
            this.ticketSection.hide();
            this.chatWrapper.hide();
            this.chatInput.hide();
            this.emailInputSection.hide();
            if (this.uuid || this.email) {
                this.refreshGuestInfo();
            }

        },
        handleStartNewChat: function () {
            this.isChatConvertedToTicket = false;
            this.refreshChatData();
            this.chatClosedSection.hide();
            this.emailInputSection.hide();
            this.addSupportMessageDOM(this.supportDeafultFirstMessageData);

            if (this.uuid || this.email) {
                this.refreshGuestInfo();
            }
            if (this.adminStatus === 'offline') {
                this.ticketSection.show();
                this.newChatSection.show();
                this.ongoingChatSection.hide();
                this.thankYouSection.hide();
                this.chatWrapper.hide();
                this.chatInput.hide();
            } else if (this.adminStatus === 'online') {
                this.ticketSection.hide();
                this.chatWrapper.show();
                this.chatInput.show();
            }
        },
        refreshChatData: function () {
            this.storedChatMessages = [];
            this.countUnreadMessages = 0;
            this.newAdminMessage = false;
            this.chatMessages.empty();
            this.chatTextarea.val('');
            this.adjustTextareaHeight();
        },
        handleErrorMessage: function (errorMessages) {
            this.chatMessages.find('.message').last().remove();
            this.scrollToBottom();

            let errorMessagesSection = $('<div></div>')
                .addClass('error-message');
            for (let errorMessageText of errorMessages) {
                let errorMessage = $(`
                    <div class="text">${errorMessageText}</div>
                `);
                errorMessagesSection.append(errorMessage);
            }
            this.chatMessages.append(errorMessagesSection);
        }
    });
});
