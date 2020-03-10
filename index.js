panel.plugin('sylvainjule/bouncer', {
    created(Vue) {
        Vue.$router.onReady(function() {
            getCurrentUser(true, function() {
                checkRedirect(Vue.$router.currentRoute, undefined);
            });
        });

        Vue.$router.beforeResolve((to, from, next) => {
            if(from.path == '/login') Vue.$data.user = undefined;

            if(!Vue.$data.user) {
                getCurrentUser(false, function() {
                    checkRedirect(to, next);
                });
            }
            else {
                checkRedirect(to, next);
            }
        });


        function checkRedirect(to, next, firstLoad = false) {
            var user = Vue.$data.user;

            if(user && user.restriction) {
                if(!isAllowed(to, user)) {
                    if(next) { next(user.restriction.path); }
                    else     { Vue.$router.push(user.restriction.path); }
                }
                else {
                    if(next) {
                        if(to.path == '/logout' || to.path == '/login') Vue.$data.user = undefined;
                        next();
                    }
                }
            }
            else {
                if(next) { next(); }
            }
        }
        function getCurrentUser(safety = false, callback = false) {
            if(safety && (Vue.$router.currentRoute.path == '/login' || Vue.$router.currentRoute.path == '/')) return false;

            Vue.$api
                .get('current-user')
                .then(user => {
                    Vue.$data.user = user;
                    if(callback) callback();
                });
        }
        function isAllowed(to, user) {
            return to.name == 'Account' ||
                   to.path == '/logout' ||
                   to.path == '/login'  ||
                   to.path.slice(0, user.restriction.path.length) == user.restriction.path;
        }
    },
});
