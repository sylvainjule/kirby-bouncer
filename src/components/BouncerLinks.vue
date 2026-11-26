<template>
    <div class="bouncer-nav-container">
        <div class="bouncer-nav-inner">
            <strong>{{ $t('sylvainjule.bouncer.switch') }}:</strong>
            <div v-for="page in pages" :key="page.path" class="page">
                <k-link :to="page.path">{{ page.title }}</k-link>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {pages: { type: Array, required: true }}
};
</script>
