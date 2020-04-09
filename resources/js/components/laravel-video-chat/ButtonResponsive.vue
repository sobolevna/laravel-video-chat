<template>
    <b-button 
        :variant="variant" 
        :to="to" 
        :href="href" 
        :type="type" 
        :title="title" 
        v-b-tooltip.hover 
        :size="size"
        @click="$emit('click')">
        <b-icon :icon="icon"></b-icon>
        <span class="d-none d-md-inline">
            <slot>{{ title }}</slot>
        </span>
    </b-button>
</template>

<script>
export default {
    /**
     * @todo Атрибутам нужна пометка обязательности и значение по умолчанию
     */
    props: ['variant', 'to', 'href', 'type', 'title', 'icon', 'title', 'size']
}
</script>