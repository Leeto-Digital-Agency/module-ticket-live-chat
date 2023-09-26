define([
    'jquery',
    'uiComponent',
    'mage/translate',
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        initialize: async function (config) {
            this._super();
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
        }
    });
});
