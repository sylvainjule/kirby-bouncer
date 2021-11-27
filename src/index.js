import BouncerNav from './components/BouncerNav.vue'

panel.plugin('sylvainjule/bouncer', {
    sections: {
        bouncernav: BouncerNav
    },
    created(Vue) {
        console.log('ok')
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
