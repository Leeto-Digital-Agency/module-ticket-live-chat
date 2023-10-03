define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: async function (config) {
            let self = this;
            this._super();
            this.setupElements(config);
            this.setupWebSocket();
            this.users = await this.getUsers();
            this.attachEventHandlers();
            self.usersLoadingDiv.css('display', 'flex');
            this.displayUserList(function () {
                self.usersLoadingDiv.hide();
            });
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
            this.chatLoadingDiv = $('#chat-admin-container .chat-area .loading-container');
            this.usersLoadingDiv = $('#chat-admin-container .user-list-wrapper .loading-container');
            this.chatTextarea = $('#chat-admin-container .chat-input textarea');
            this.welcomeMessage = $('#chat-admin-container .welcome-message');
            this.fileInput = $('#chat-admin-container #file-input');
            this.statusSelect = $('#chat-admin-container #chat-status-list');
            this.statusModal = $('#status-update-modal');
            this.allowedExtensions = config.allowedExtensions.split(',');
            this.maxFilesSize = parseInt(config.maxFilesSize);
            this.getUsersUrl = config.getUsersUrl;
            this.getUserUrl = config.getUserUrl;
            this.updateChatHeaderUrl = config.updateChatHeaderUrl;
            this.changeChatStatusControllerUrl = config.changeChatStatusControllerUrl;
            this.updateUnreadChatMessages = config.updateUnreadChatMessages;
            this.userAvatarImagePath = config.userAvatarImagePath;
            this.resourceId = null;
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

                if (data.resourceId) {
                    self.resourceId = data.resourceId;
                    return;
                }
                if (data.notifyAdminsUserClick){
                    let userItem = $(`.user-item[data-chat-id="${data.chatId}"]`);
                    userItem.trigger('click');
                    return;
                }
                if (data.typeEvent) {
                    self.handleTyping(data);
                    return;
                }
                if (data.errorMessages) {
                    self.handleErrorMessage(data.errorMessages);
                    return;
                }
                if(data.lostMessages) {
                    self.handleLostMessages(data.messages);
                    return;
                }

                let chatId = data.chatId;
                if (!chatId) {
                    return;
                }
                self.createOrUpdateUser(chatId, data);

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

            this.chatTextarea.on('keyup', function () {
                if (!self.chatTextarea.val().length) {
                    self.conn.send(JSON.stringify({
                        typing: false,
                        chatId: self.selectedChatId,
                        isAdmin: true,
                        typingEvent: true
                    }));
                } else {
                    self.conn.send(JSON.stringify({
                        typing: true,
                        chatId: self.selectedChatId,
                        isAdmin: true,
                        typingEvent: true
                    }));
                }
            });

            this.chatTextarea.on('blur', function () {
                self.conn.send(JSON.stringify({
                    typing: false,
                    chatId: self.selectedChatId,
                    isAdmin: true,
                    typingEvent: true
                }));
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
        createOrUpdateUser: function (chatId, data) {
            let self = this;

            let user = self.users.find(user => user.id == chatId);
            if (user) {
                self.updateUser(user, data);
                if (chatId == self.selectedChatId) {
                    self.notifyAdminsUserClick();
                    let message = self.getLatestMessage(); 
                    self.appendMessage(message)
                    self.updateUnreadMessages();
                } else {
                    self.displayUnreadMessages(user);
                }
            } else {
                self.createUser(data);
            }
        },
        handleUserItemClick: async function (event) {
            let self = this;
            let userItem = $(event.currentTarget);
            if (userItem.attr('data-chat-id') == self.selectedChatId) {
                return;
            }
            self.chatLoadingDiv.css('display', 'flex');
            $('.user-item').removeClass('selected');
            self.welcomeMessage.hide();
            self.chatArea.show();
            userItem.addClass('selected');
            self.selectedChatId = parseInt(userItem.attr('data-chat-id'));
            self.notifyAdminsUserClick();
            self.displayMessages();
            self.updateUnreadMessages();
            self.updateChatHeader(function() {
                self.chatLoadingDiv.hide();
            });
            self.scrollToBottom();
        },
        handleChatStatusUpdate: function (previousSelectedOption, target) {
            let self = this;
            self.statusModal.show();

            self.statusModal.find('#modalYesBtn').off('click');
            self.statusModal.find('#modalNoBtn').off('click');
            self.statusModal.find('#modalYesBtn').on('click', function () {
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
                        self.removeUser();
                        self.totalChats.text(self.users.length);
                        self.selectedChatId = null;
                        self.displayWelcomeMessage();
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            });
            self.statusModal.find('#modalNoBtn').on('click', function () {
                target.val(previousSelectedOption);
                self.statusModal.hide();
            });
        },
        handleSendMessage: function () {
            let self = this;

            self.clearErrorMessage();
            let messageText = self.chatTextarea.val().trim();
            if (self.validateTextarea()) {
                return;
            }
            // Send message to server
            let data = {
                newMessage: true,
                fromId: null,
                isAdmin: true,
                email: null,
                resourceId: self.resourceId,
                message: messageText,
                chatId: self.selectedChatId,
                type: "text",
            }
            self.conn.send(JSON.stringify(data));

            // store the message in the array
            self.pushMessage(data)

            // Display the message in the chat
            self.chatMessages.append($('<div></div>').addClass('message-chat admin')
                .html(messageText.replace(/\n/g, '<br>')));
            self.chatTextarea.val('');
            self.adjustTextareaHeight();
            self.adjustLatestMessage();
            self.scrollToBottom();
        },
        handleTextareaShiftEnter: function (event) {
            let self = this;

            let startPos = self.chatTextarea[0].selectionStart;
            let endPos = self.chatTextarea[0].selectionEnd;
            let text = self.chatTextarea.val();
            let newText = text.substring(0, startPos) + '\n' + text.substring(endPos);

            self.chatTextarea.val(newText);
            self.chatTextarea[0].selectionStart = self.chatTextarea[0].selectionEnd = startPos + 1;
            self.adjustTextareaHeight();
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
        displayUserList: function (callback) {
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
            if (callback && typeof callback === 'function') {
                callback();
            }
        },
        appendUser: function (user) {
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
            
            // userItem.insertBefore(this.userList.children()[0]);
            this.userList.prepend(userItem);
        },
        removeUser: function(user = false) {
            let chatId = user ? user.id : this.selectedChatId;
            let userItem = $(`.user-item[data-chat-id="${chatId}"]`);
            userItem.remove();
        },
        updateUser: function (user, data) {
            let self = this;
            self.pushMessageFromUser(data, user);
            self.adjustLatestMessage(user);
            user.unreadMessages = user.unreadMessages ? user.unreadMessages + 1 : 1;
            self.changePosition(user.id);
        },
        createUser: function (data) {
            let self = this;
            
            $.ajax({
                url: self.getUserUrl,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    chatId: data.chatId
                },
                success: function (response) {
                    let user = response.user;
                    self.users.unshift(user);
                    self.totalChats.text(self.users.length);
                    self.appendUser(user);
                }
            });
        },
        changePosition: function (chatId) {
            // change position array:
            let user = this.users.find(user => user.id == chatId);
            this.users = this.users.filter(user => user.id != chatId);
            this.users.unshift(user);

            // change position DOM:
            let userItem = $(`.user-item[data-chat-id="${chatId}"]`);
            userItem.remove();
            this.userList.prepend(userItem);
        },
        displayUnreadMessages: function(user) {
            let userItem = $(`.user-item[data-chat-id="${user.id}"]`);
            let unreadMessages = userItem.find('.unread-messages');

            if (unreadMessages.length) {
                unreadMessages.text(user.unreadMessages);
            } else {
                unreadMessages = $('<div></div>')
                    .append($('<span></span>')
                    .addClass('unread-messages')
                    .text(user.unreadMessages));
                userItem.append(unreadMessages);
            }
        },
        adjustLatestMessage: function (user = false) {
            let chatId = user ? user.id : this.selectedChatId;
            let latestMessage = this.getLatestMessage(user);
            let userItem = $(`.user-item[data-chat-id="${chatId}"]`);

            if (latestMessage.sender === 'user') {
                userItem.find('.latest-message').text(latestMessage.text);
            } else {
                userItem.find('.latest-message').text($t('Sent'));
            }
        },

        getLatestMessage: function (user = false) {
            let selectedUser = user ? user : this.getSelectedUser();
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
                resourceId: self.resourceId,
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
            let convertedMaxFilesSize = this.maxFilesSize * 1024 * 1024;
            let fileNameParts = file.name.split('.');
            let fileExtension = fileNameParts[fileNameParts.length - 1].toLowerCase();

            return this.allowedExtensions.includes(fileExtension) && file.size <= convertedMaxFilesSize;
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
                text.text($t('Message cannot be empty.'));
                error = true;

            }  else if (messageText.length > MAX_LENGTH) {
                text.text(
                    $t('Message length cannot exceed %1 characters.')
                    .replace('%1', MAX_LENGTH)
                );
                error = true;
            }

            if (error) {
                this.scrollToBottom();
                this.chatArea.append(errorMessage);
            }            

            return error;
        },
        // Function to fetch data via AJAX and update chat-header
        fetchChatHeaderData: function (callback) {
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
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
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
                    messageDiv.html(message.text.replace(/\n/g, '<br>'));
                }

                this.chatMessages.append(messageDiv);
            });
            this.scrollToBottom();
        },
        appendMessage: function(message) {
            this.clearErrorMessage();
            let messageDiv = $('<div></div>')
                .addClass(`message-chat ${message.sender}`);

            if (message.type === 'file') {
                messageDiv.append(this.renderFile(message.originalName, message.path));

            } else {
                messageDiv.html(message.text.replace(/\n/g, '<br>'));
            }
            this.chatMessages.append(messageDiv);
            this.scrollToBottom();
        },
        pushMessage: function (data, user = false) {
            let selectedUser;

            if (user) {
                selectedUser = user
            } else {
                selectedUser = this.getSelectedUser();
            }
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
        pushMessageFromUser: function (data, user) {
            if (data.type === "text") {
                user.messages.push({
                    sender: data.sender,
                    type: data.type,
                    text: data.message
                });
            } else if (data.type === 'file') {
                user.messages.push({
                    sender: data.sender,
                    type: data.type,
                    originalName: data.originalName,
                    path: data.path
                });
            }
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
        updateChatHeader: function (callback = false) {
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
            createdAtElement.text(selectedUser.chat.createdAt);
            statusDiv.find("option").prop('selected', false); 
            statusDiv.find("[value='" + selectedUser.chat.statusId + "']").prop('selected', true);
            statusDiv.removeClass().addClass(selectedUser.chat.statusClass)
            if (callback && typeof callback === 'function') {
                callback();
            }
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
                        self.getSelectedUser().unreadMessages = 0;
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
            let selectedUser = this.getSelectedUser();
            selectedUser.messages.pop();
            this.adjustLatestMessage();
            this.scrollToBottom();
        },
        handleTyping: function (data) {
            let self = this;

            if (data.chatId != this.selectedChatId) {
                return;
            }
            let isShown = $("#chat-admin-container .typing-event").length;
            if (!isShown && data.typing) {
               self.showTypingMessage();
               self.scrollToBottom();
            } else if (isShown && !data.typing) {
                self.hideTypingMessage();
            }
        },
        handleLostMessages: function (messages) {
            let self = this;

            for (let message of messages) {
                self.createOrUpdateUser(message.chatId, message);
            }
        },
        showTypingMessage: function () {
            let typingMessage = $('<div></div>')
                .addClass('typing-event')
                .text($t('User is typing...'));

            this.chatMessages.append(typingMessage);
        },
        hideTypingMessage: function () {
            $("#chat-admin-container .typing-event").remove();
        },
        notifyAdminsUserClick: function() {
            this.conn.send(JSON.stringify({
                notifyAdminsUserClick: true,
                chatId: this.selectedChatId,
                resourceId: this.resourceId
            }));
        }
    });
});
