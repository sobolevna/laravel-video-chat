<template>
    <div class="col">
        <img :src="file.file_details.webPath" class="img-responsive" :alt="file.name">
        <a class="btn btn-success" :href="file.file_details.webPath" target="_blank" download :title="file.name">Скачать</a>
    </div>
</template>

<script>
    export default {
        props: ['file']
    }
</script>
