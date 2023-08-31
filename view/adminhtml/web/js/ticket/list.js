define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            this.setupElements();
            this.attachEventHandlers();
            this.users = this.getUsers();
            this.selectedUserId = null;
            this.displayUserList();
            this.displayWelcomeMessage();
        },

        setupElements: function () {
            this.userList = $('#ticket-admin-container .user-list');
            this.totalChats = $('#ticket-admin-container .total-chats');
            this.chatArea = $('#ticket-admin-container .chat-area');
            this.chatMessages = $('#ticket-admin-container .chat-messages');
            this.userName = $('#ticket-admin-container .user-name');
            this.chatTextarea = $('#ticket-admin-container .chat-input textarea');
            this.welcomeMessage = $('#ticket-admin-container .welcome-message');
            this.fileInput = $('#ticket-admin-container #file-input');
        },

        attachEventHandlers: function () {
            var self = this;

            this.userList.on('click', '.user-item', $.proxy(this.handleUserItemClick, this));
            $('.chat-input button').on('click', $.proxy(this.handleSendMessage, this));

            this.chatTextarea.on('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    self.handleSendMessage();
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
        getUsers: function () {
            let users = [
                {
                    id: 1,
                    name: 'User 1',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hi there!' },
                        { sender: 'admin', text: 'Hello! How can I assist you?' }
                    ]
                },
                {
                    id: 2,
                    name: 'User 2',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 3,
                    name: 'User 3',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' },
                        { sender: 'user', text: 'i need help!' },
                        { sender: 'user', text: 'i need help!' },
                    ]
                },
                {
                    id: 4,
                    name: 'User 4',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' },
                        { sender: 'user', text: 'i need help!' },
                        { sender: 'user', text: 'hajde ktu m!' },
                    ]
                },
                {
                    id: 5,
                    name: 'User 5',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 6,
                    name: 'User 6',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 7,
                    name: 'User 7',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 8,
                    name: 'User 8',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 9,
                    name: 'User 9',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 10,
                    name: 'User 10',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                },
                {
                    id: 11,
                    name: 'User 11',
                    avatar: 'https://www.pngitem.com/pimgs/m/78-786501_black-avatar-png-user-icon-png-transparent-png.png',
                    messages: [
                        { sender: 'user', text: 'Hey, I have a question.' },
                        { sender: 'admin', text: 'Sure, go ahead!' }
                    ]
                }
            ]

            return users;
        },

        handleUserItemClick: function (event) {
            this.welcomeMessage.hide();
            this.chatArea.show();
            $('.user-item').removeClass('selected');
            let userItem = $(event.currentTarget);
            userItem.addClass('selected');
            this.selectedUserId = parseInt(userItem.attr('data-chat-id'));
            this.displayMessages();
        },

        handleSendMessage: function () {
            this.clearErrorMessage();
            let messageText = this.chatTextarea.val().trim();

            if (this.validateTextarea()) {
                return;
            }
            
            let selectedUser = this.getSelectedUser();
            selectedUser.messages.push({ sender: 'admin', text: messageText });
            this.displayMessages();
            this.chatTextarea.val('');
            this.adjustTextareaHeight();
            this.scrollToBottom();
            this.adjustLatestMessage();
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
                    .append($('<img>').attr('src', user.avatar).attr('alt', user.name));

                let userDetails = $('<div></div>')
                    .addClass('user-details')
                    .append($('<h3></h3>').text(user.name))
                    .append($('<p class="latest-message"></p>'));
                
                let latestMessage = user.messages[user.messages.length - 1];
                
                if (latestMessage.sender === 'user') {
                    userDetails.find('.latest-message').text(latestMessage.text);
                } else {
                    userDetails.find('.latest-message').text('Sent');
                }

                userItem.append(userAvatar).append(userDetails);
                this.userList.append(userItem);
            });

            this.totalChats.text(this.users.length);
        },
        adjustLatestMessage: function () {
            let selectedUser = this.getSelectedUser();
            let latestMessage = selectedUser.messages[selectedUser.messages.length - 1];
            let userItem = $(`.user-item[data-chat-id="${this.selectedUserId}"]`);

            if (latestMessage.sender === 'user') {
                userItem.find('.latest-message').text(latestMessage.text);
            } else {
                userItem.find('.latest-message').text('Sent');
            }
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

            let selectedUser = this.getSelectedUser();
            let message = this.createFileMessage(file);
            selectedUser.messages.push({ sender: 'admin', text: message, type: 'image' });
            this.displayMessages();
            this.scrollToBottom();
        },

        createFileMessage: function (file) {
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

            return fileContent;
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
            this.chatMessages.empty();
            this.clearErrorMessage();

            let selectedUser = this.getSelectedUser();
            this.userName.text(selectedUser.name);

            selectedUser.messages.forEach(message => {
                let messageDiv = $('<div></div>')
                    .addClass(`message-chat ${message.sender}`);

                if (message.type === 'image') {
                    messageDiv.append(message.text);

                } else {
                    messageDiv.text(message.text);
                }

                this.chatMessages.append(messageDiv);
            });
        },

        displayWelcomeMessage: function () {
            this.chatArea.hide();
            this.welcomeMessage.show();
            this.welcomeMessage.text($t('Select a chat and start messaging now...'));
        },

        scrollToBottom: function () {
            this.chatMessages[0].scrollTop = this.chatMessages.prop('scrollHeight');
        },

        clearErrorMessage: function () {
            $('.error-message').remove();
        },

        getSelectedUser: function () {
            return this.users.find(user => user.id === this.selectedUserId);
        }
    });
});
