<template>
    <div v-if="show" class="bouncer-nav">
        <div class="bouncer-nav-container">
            <div class="bouncer-nav-inner">
                <strong>Basculer vers :</strong>
                <div v-for="page in pages" class="page">
                    <k-link :to="page.path">{{ page.title }}</k-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            user: undefined
        }
    },
    created() {
        this.$api.get('current-user').then(user => {
            this.user = user;
            if(this.showBar(user)) {
                this.$nextTick(() => {
                    let _panel   = this.$root.$el
                    let _main    = _panel.querySelector('.k-panel-main')
                    let _bar     = _panel.querySelector('.k-sections .bouncer-nav-container')
                    let _prevBar = document.querySelector('.k-panel > .bouncer-nav-container')

                    _panel.classList.add('bouncer-padding-top')
                    _prevBar && _prevBar.remove()
                    _panel.insertBefore(_bar, _main)
                })
            }
        });
    },
    computed: {
        show() {
            return this.showBar(this.user)
        },
        pages() {
            if(!this.show) return []
            return this.user.allowed.filter(el => { return el.path != this.parent})
        }
    },
    methods: {
        showBar(user) {
            return user && user.nav && Array.isArray(user.allowed) && user.allowed.length > 1
        }
    }
};
</script>

<style lang="scss">
  @import '../assets/css/styles.scss';
</style>
