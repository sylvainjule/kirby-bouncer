<template>
    <div v-if="show" class="bouncer-nav">
        <div class="bouncer-nav-inner">
            <strong>Basculer vers :</strong>
            <div v-for="page in pages" class="page">
                <k-link :to="page.path">{{ page.title }}</k-link>
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
                this.$root.$el.classList.add('bouncer-padding-top')
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
.bouncer-padding-top .k-page-view {
    padding-top: 40px;
}
.bouncer-nav {
    position: fixed;
    top: 0;
    width: 100%;
    left: 0;
    height: 40px;
    background: lighten(#81a3be, 15%);
    display: flex;
    align-items: center;
    font-size: 0.75rem;
    padding-bottom: 0;
    &-inner {
        width: 100%;
        padding: 0 3rem;
        margin: 0 auto;
        max-width: 100rem;
        strong {
            margin-right: 8px;
        }
        .page {
            display: inline-block;
        }
        .page + .page {
            &:before {
                content: '–';
                margin-left: 8px;
                margin-right: 8px;
            }
        }
        a {
            border-bottom: 1px solid rgba(0, 0, 0, .15);
            &:hover {
                border-color: black;
            }
        }
    }
}
</style>
