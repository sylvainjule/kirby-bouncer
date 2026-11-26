<template>
    <div ref="anchor" class="bouncer-nav-anchor" :class="{ 'bouncer-nav-inline': show && !host }">
        <Teleport v-if="show && host" :to="host">
            <BouncerLinks :pages="pages" />
        </Teleport>
        <BouncerLinks v-else-if="show" :pages="pages" />
    </div>
</template>

<script>
import BouncerLinks from './BouncerLinks.vue';
import { visiblePages, showBar, mountHost } from '../navigation.js';

export default {
    components: { BouncerLinks },
    props: {
        bouncerUser:   { type: Object, default: () => ({}) },
        bouncerParent: { type: String, default: '' }
    },
    data() {
        return { host: null }
    },
    computed: {
        show() {
            return showBar(this.bouncerUser)
        },
        pages() {
            return visiblePages(this.bouncerUser, this.bouncerParent)
        }
    },
    watch: {
        show() {
            this.syncHost()
        }
    },
    mounted() {
        this.syncHost()
    },
    activated() {
        this.syncHost()
    },
    deactivated() {
        this.releaseHost()
    },
    beforeUnmount() {
        this.releaseHost()
    },
    methods: {
        syncHost() {
            if(!this.show) {
                this.releaseHost()
            } 
            else if(!this.host?.isConnected) {
                this.host = mountHost(this.$refs.anchor)
            }
        },
        releaseHost() {
            this.host?.remove()
            this.host = null
        }
    }
};
</script>

<style lang="scss">
    @import '../assets/css/styles.scss';
</style>