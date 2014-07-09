'use strict';

ratApp.service('employerSvc',
    function() {
        // The service
        var service = {

            // Status of current session
            status: function() {
                return status;
            },

            // Reset session
            logout: function() {
                rat.userStorage.remove();
                rat.tokenStorage.remove();

            },


            // Start new session / Login user
            instance: function(token,user) {
                rat.tokenStorage.set(token);
                rat.userStorage.set(JSON.stringify(user));
            },

            // check login status
            isLogin: function() {
                var token = rat.tokenStorage.get();
                var user = rat.userStorage.get();
                if(!user) return false;

                user = JSON.parse(user);
                if(token && user.id) return true;
                return false;
            },
            // check login status
            current: function() {

                var user = rat.userStorage.get();
                if(!user) return null;

                user = JSON.parse(user);
                if(user.id) return user;
                return null;
            }

        };

        return service;
    });


