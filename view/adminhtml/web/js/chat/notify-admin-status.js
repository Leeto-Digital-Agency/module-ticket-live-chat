define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: async function (config) {
            this._super();
            this.chatUnreadMessagesUrl = config.chatUnreadMessagesUrl;
            this.leetoMenu = $('.item-leeto-menu');
            this.chatMenuItem = $('.item-leeto-menu .item-leeto-chat.level-1');
            this.checkChatUnreadMessages();
            if (config.isAdminLoggedIn) {
                this.setupElements(config);
                this.setupWebSocket();
            }
        },

        setupElements: function (config) {
            this.conn = new WebSocket(`ws://${config.webBaseUrl}:${config.webSocketPort}`);
        },

        setupWebSocket: function () {
            let self = this;

            self.conn.onopen = () => {
                self.conn.send(JSON.stringify({
                    newConnection: true,
                    role: "admin"
                }));
            };
            self.conn.onmessage = (event) => {
                let data = JSON.parse(event.data);
                if (data.newMessage) {
                    self.checkChatUnreadMessages();
                }
            };
        },

        checkChatUnreadMessages: function() {
            var self = this;
            $.ajax({
                url: self.chatUnreadMessagesUrl,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    test: 1
                },
                success: function (response) {
                    if (response.unreadMessages) {
                        self.leetoMenu.addClass('unread-messages');
                        self.leetoMenu.find('a:first').addClass('unread-messages');
                        self.chatMenuItem.find('a:first').addClass('unread-messages');
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        }
    });
});
