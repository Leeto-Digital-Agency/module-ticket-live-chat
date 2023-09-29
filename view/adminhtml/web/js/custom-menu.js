define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            this._super();
            this.ticketUnreadMessagesUrl = config.ticketUnreadMessagesUrl;
            this.leetoMenu = $('.item-leeto-menu');
            this.ticketMenuItem = $('.item-leeto-menu .item-leeto-tickets.level-1');
            if (config.isAdminLoggedIn) {
                this.checkTicketUnreadMessages();
            }
        },

        checkTicketUnreadMessages: function() {
            var self = this;
            $.ajax({
                url: self.ticketUnreadMessagesUrl,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    test: 1
                },
                success: function (response) {
                    if (response.count > 0) {
                        self.leetoMenu.addClass('unread-messages');
                        self.leetoMenu.find('a:first').addClass('unread-messages');
                        self.ticketMenuItem.find('a:first').addClass('unread-messages');
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        },
    });
});
