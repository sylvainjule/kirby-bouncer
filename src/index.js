import BouncerNav from './components/BouncerNav.vue'

panel.plugin('sylvainjule/bouncer', {
    sections: {
        bouncernav: BouncerNav
    },
    created(Vue) {
        Vue.$store
            .dispatch("system/load")
            .then(system => {
                // prevents an infinite loop on login page when there is no account
                if(!system.isReady) return true;

                watchUser(Vue);

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

                    if(user && Array.isArray(user.restriction) && user.restriction.length) {
                        if(!isAllowed(to, user)) {
                            if(next) { next(user.restriction[0].path); }
                            else     { Vue.$router.push(user.restriction[0].path); }
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
                        })
                        .catch(error => {
                            if(callback) callback();
                        });
                }
                function isAllowed(to, user) {
                    let allowed = to.name == 'Account' || to.path == '/account' || to.path == '/logout' || to.path == '/login';

                    if(!allowed && Array.isArray(user.restriction)) {
                        user.restriction.forEach(page => {
                            if(to.path.slice(0, page.path.length) == page.path) {
                                allowed = true;
                            }
                        })
                    }

                    return allowed;
                }
            })
    },
});

function watchUser(app) {
    app.$router.afterEach((to, from) => {
        updateUser(app)
    });
    app.$store.watch(
        state => {
            if (state.user.current) { return state.user.current; }
        },
        (newUser, oldUser) => {
            updateUser(app)
        }
    )
}
function updateUser(app, user) {
    var user = app.$data.user

    app.$el.classList.remove('bouncer-padding-top');

    if(user && user.nav && Array.isArray(user.restriction) && user.restriction.length > 1) {
        var paths = user.restriction.map(el => { return el.path })

        if(paths.includes(app.$router.currentRoute.path)) {
            app.$el.classList.add('bouncer-padding-top');
        }
    }
}
