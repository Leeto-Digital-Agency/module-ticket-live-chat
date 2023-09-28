define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: async function (config) {
            this._super();
            this.setupElements(config);
            this.setupWebSocket();
            this.users = await this.getUsers();
            this.attachEventHandlers();
            this.displayUserList();
            this.displayWelcomeMessage();
            this.selectedChatId = null;
        },
        setupElements: function (config) {
            this.conn = new WebSocket("ws://" + config.webBaseUrl + ":" + config.webSocketPort);
            this.userList = $('#chat-admin-container .user-list');
            this.totalChats = $('#chat-admin-container .total-chats');
            this.chatArea = $('#chat-admin-container .chat-area');
            this.chatMessages = $('#chat-admin-container .chat-messages');
            this.userName = $('#chat-admin-container .user-name');
            this.chatTextarea = $('#chat-admin-container .chat-input textarea');
            this.welcomeMessage = $('#chat-admin-container .welcome-message');
            this.fileInput = $('#chat-admin-container #file-input');
            this.statusSelect = $('#chat-admin-container #chat-status-list');
            this.statusModal = $('#status-update-modal');
            this.getUsersUrl = config.getUsersUrl;
            this.updateChatHeaderUrl = config.updateChatHeaderUrl;
            this.changeChatStatusControllerUrl = config.changeChatStatusControllerUrl;
            this.updateUnreadChatMessages = config.updateUnreadChatMessages;
            this.userAvatarImagePath = config.userAvatarImagePath;
        },
        setupWebSocket: function () {
            let self = this;

            self.conn.onopen = () => {
                self.conn.send(JSON.stringify({
                    newConnection: true,
                    role: "admin"
                }));
            };
            self.conn.onmessage = async (event) => {
                let data = JSON.parse(event.data);

                if (data.errorMessages) {
                    self.handleErrorMessage(data.errorMessages);
                    return;
                }
                self.users = await self.getUsers();
                self.displayUserList();
                if (self.selectedChatId) {
                    let userItem = $(`.user-item[data-chat-id="${self.selectedChatId}"]`);
                    userItem.addClass('selected');
                    userItem.find('.unread-messages').remove();
                    self.updateUnreadMessages()
                    self.displayMessages();
                    self.scrollToBottom();
                }
                console.log('Received message:', event.data);
                 // TODO:
                // let data = JSON.parse(event.data);
                // let chatId = data.chatId;
                // let user = this.users.find(user => user.id == chatId);
                // if (user) {
                //     user.messages.push(data);
                // }
            };
            self.conn.onclose = (event) => {
                console.log('WebSocket connection closed:', event.reason);
            };
            self.conn.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        },
        attachEventHandlers: function () {
            var self = this;

            this.userList.on('click', '.user-item', function (event) {
                self.handleUserItemClick(event);
            });

            $('.chat-input button').on('click', function (event) {
                self.handleSendMessage(event);
            });

            this.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    self.handleSendMessage();
                } else if (event.key === 'Enter' && event.shiftKey) {
                    event.preventDefault();
                    self.handleTextareaShiftEnter(event);
                }
            });

            let previousSelectedOption;
            this.statusSelect.on('focus', function () {
                previousSelectedOption = $(this).val();
            }).change(function() {
                self.handleChatStatusUpdate(previousSelectedOption, $(this));
            });

            this.chatTextarea.on('input', function () {
                self.adjustTextareaHeight();
            });

            this.fileInput.on('change', function () {
                self.handleFileAttachment();
            });
        },
        getUsers: async function () {
            let response;

            try {
                response = await $.ajax({
                    url: this.getUsersUrl,
                    method: "GET",
                    dataType: "json"
                });

            } catch (error) {
                console.log(error);
                return;
            }
            
            return response.users;
        },
        handleUserItemClick: async function (event) {
            let userItem = $(event.currentTarget);
            $('.user-item').removeClass('selected');
            this.welcomeMessage.hide();
            this.chatArea.show();
            userItem.addClass('selected');
            this.selectedChatId = parseInt(userItem.attr('data-chat-id'));
            this.displayMessages();

            await this.updateUnreadMessages();
            this.fetchChatHeaderData();
            this.scrollToBottom();
        },
        handleChatStatusUpdate: function (previousSelectedOption, target) {
            let self = this;
            this.statusModal.show();

            this.statusModal.find('#modalYesBtn').off('click');
            this.statusModal.find('#modalNoBtn').off('click');
            this.statusModal.find('#modalYesBtn').on('click', function () {
                let statusValue = target.val();
                $.ajax({
                    url: self.changeChatStatusControllerUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: { 
                        chatId: self.selectedChatId,
                        statusValue: statusValue
                    },
                    success: function (response) {
                        self.notifyUserForClosedChat(response.ticketUrl);
                        self.statusModal.hide();
                        self.users = self.users.filter(user => user.id != self.selectedChatId);
                        self.displayUserList();
                        self.selectedChatId = null;
                        self.displayWelcomeMessage();
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
        handleSendMessage: function () {
            this.clearErrorMessage();

            let messageText = this.chatTextarea.val().trim();
            if (this.validateTextarea()) {
                return;
            }
            // Send message to server
            let data = {
                newMessage: true,
                fromId: null,
                isAdmin: true,
                email: null,
                message: messageText,
                chatId: this.selectedChatId,
                type: "text",
            }
            this.conn.send(JSON.stringify(data));

            // store the message in the array
            this.pushMessage(data)

            // Display the message in the chat
            this.chatMessages.append($('<div></div>').addClass('message-chat admin').text(messageText));
            this.chatTextarea.val('');
            this.adjustTextareaHeight();
            this.adjustLatestMessage();
            this.scrollToBottom();
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
        displayUserList: function () {
            this.userList.empty();

            this.users.forEach(user => {
                let userItem = $('<div></div>')
                    .addClass('user-item')
                    .attr('data-chat-id', user.id);
                
                let userAvatar = $('<div></div>')
                    .addClass('user-avatar')
                    .append($('<img>').attr('src', this.userAvatarImagePath).attr('alt', user.name));

                let userDetails = $('<div></div>')
                    .addClass('user-details')
                    .append($('<h3></h3>').text(user.name))
                    .append($('<p class="latest-message"></p>'));

                let userContainer = $('<div></div>')
                    .addClass('d-flex')
                    .append(userAvatar)
                    .append(userDetails);
                
                let userUnreadMessages = $('<div></div>')
                    .append($('<span></span>')
                    .addClass('unread-messages')
                    .text(user.unreadMessages));
                
                let latestMessage = user.messages[user.messages.length - 1];
                
                if (latestMessage.sender === 'user') {
                    if (latestMessage.type === 'file') {
                        userDetails.find('.latest-message').text($t('Attachment'));
                    } else {
                        userDetails.find('.latest-message').text(latestMessage.text);
                    }
                } else {
                    userDetails.find('.latest-message').text($t('Sent'));
                }

                userItem.append(userContainer);
                if (user.unreadMessages > 0) { 
                    userItem.append(userUnreadMessages);
                }
                
                this.userList.append(userItem);
            });

            this.totalChats.text(this.users.length);
        },
        adjustLatestMessage: function () {
            let latestMessage = this.getLatestMessage();
            let userItem = $(`.user-item[data-chat-id="${this.selectedChatId}"]`);

            if (latestMessage.sender === 'user') {
                userItem.find('.latest-message').text(latestMessage.text);
            } else {
                userItem.find('.latest-message').text('Sent');
            }
        },

        getLatestMessage: function () {
            let selectedUser = this.getSelectedUser();
            let latestMessage = selectedUser.messages[selectedUser.messages.length - 1];
            
            return latestMessage;
        },
        handleFileAttachment: function () {
            this.clearErrorMessage();

            let file = this.fileInput[0].files[0];
            if (!this.validateFile(file)) {
                let errorMessage = $('<div class="error-message"><div class="text">Invalid file type or file size exceeds the limit.</div></div>');
                this.chatArea.append(errorMessage);
                this.scrollToBottom();
                this.fileInput.val('');
                return;
            }

            // Read the file and convert it to base64
            let reader = new FileReader();
            let data = {
                newMessage: true,
                fromId: null,
                isAdmin: true,
                email: null,
                message: null,
                chatId: this.selectedChatId,
                type: "file",
                attachment: {
                    name: file.name,
                    type: file.type,
                    size: file.size
                }
            };

            reader.onload = (event) => {
                let base64FileData = event.target.result.split(',')[1]; // Extract the base64-encoded part
                data.attachment.data = base64FileData;
                this.conn.send(JSON.stringify(data));
            };
            reader.readAsDataURL(file);
            
            // store the message in the array
            data.file = file;
            this.pushMessage(data)

            // Display the file in the chat as a message
            let fileMessage = this.createFileMessage(file);
            this.chatMessages.append(fileMessage);
            this.scrollToBottom();
            this.adjustLatestMessage();
        },
        createFileMessage: function (file) {
            let messageDiv = $('<div></div>')
                    .addClass(`message-chat admin`);
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
            messageDiv.append(fileContent);

            return messageDiv[0];
        },
        validateFile: function (file) {
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            let maxFileSize = 3 * 1024 * 1024; // 3 MB in bytes
            let fileNameParts = file.name.split('.');
            let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();

            return allowedExtensions.includes(fileExtension) && file.size <= maxFileSize;
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
        getFileType: function (originalName) {
            const fileExtension = originalName.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

            if (imageExtensions.includes(fileExtension)) {
                return 'image';
            }

            return 'file';
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
        // Function to fetch data via AJAX and update chat-header
        fetchChatHeaderData: function () {
            var self = this;

            $.ajax({
                url: this.updateChatHeaderUrl,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    chatId: self.selectedChatId
                },
                success: function (response) {
                    self.updateChatHeader(response);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        displayMessages: function () {
            this.chatMessages.empty();
            this.clearErrorMessage();
            let selectedUser = this.getSelectedUser();

            selectedUser.messages.forEach(message => {
                let messageDiv = $('<div></div>')
                    .addClass(`message-chat ${message.sender}`);

                if (message.type === 'file') {
                    messageDiv.append(this.renderFile(message.originalName, message.path));

                } else {
                    messageDiv.text(message.text);
                }

                this.chatMessages.append(messageDiv);
            });
            this.scrollToBottom();
        },
        pushMessage: function (data) {
            let selectedUser = this.getSelectedUser();

            if (data.type === "text") {
                selectedUser.messages.push({
                    sender: "admin",
                    type: data.type,
                    text: data.message
                });
            } else if (data.type === 'file') {
                selectedUser.messages.push({
                    sender: "admin",
                    type: data.type,
                    originalName: data.file.name,
                    path: URL.createObjectURL(data.file)
                });
            }
            selectedUser.messages.push();
        },
        displayWelcomeMessage: function () {
            this.chatArea.hide();
            this.welcomeMessage.show();
            this.welcomeMessage.text($t('Select a chat and start messaging now...'));
        },
        scrollToBottom: function () {
            setTimeout(() => {
                this.chatMessages[0].scrollTop = this.chatMessages.prop('scrollHeight');
            }, 30);

        },
        clearErrorMessage: function () {
            $('.error-message').remove();
        },
        getSelectedUser: function () {
            return this.users.find(user => user.id == this.selectedChatId);
        },
        updateChatHeader: function (data) {
            let selectedUser = this.getSelectedUser();
            let customerNameElement = $('.chat-customer .customer');
            let customerEmailElement = $('.chat-customer .email');
            let createdAtElement = $('.chat-date .date');
            let statusDiv = $('.chat-status #status-update');

            if (selectedUser.isGuest) {
                customerNameElement.text(selectedUser.name);
            }else {
                let customerUrlTag = $("<a></a>");
                customerUrlTag.attr("href", selectedUser.customerUrl);
                customerUrlTag.attr("target", "_blank");
                customerUrlTag.text(selectedUser.name);
                customerNameElement.html(customerUrlTag);
            }
            
            customerEmailElement.text(selectedUser.email);
            createdAtElement.text(data.createdAt);
            statusDiv.find("option").prop('selected', false); 
            statusDiv.find("[value='" + data.statusId + "']").prop('selected', true);
            // statusDiv.text(data.statusLabel);
            statusDiv.removeClass().addClass(data.status)
        },
        updateUnreadMessages: function () {
            let self = this;
            
            $.ajax({
                url: this.updateUnreadChatMessages,
                type: 'POST',
                dataType: 'JSON',
                data: { 
                    chatId: this.selectedChatId,
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Unread messages updated');
                        let userItem = $(`.user-item[data-chat-id="${self.selectedChatId}"]`);
                        userItem.find('.unread-messages').remove();
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
        notifyUserForClosedChat: function (ticketUrl) {
            this.conn.send(JSON.stringify({
                chatClosed: true,
                byAdmin: true,
                chatId: this.selectedChatId,
                ticketUrl: ticketUrl
            }));
        },
        handleErrorMessage: function (errorMessages) {
            this.chatMessages.find('.message-chat').last().remove();
            this.scrollToBottom();

            let errorMessagesSection = $('<div></div>')
                .addClass('error-message');
            for (let errorMessageText of errorMessages) {
                let errorMessage = $(`
                    <div class="text">${errorMessageText}</div>
                `);
                errorMessagesSection.append(errorMessage);
            }
            this.chatArea.append(errorMessagesSection);
        }
    });
});
