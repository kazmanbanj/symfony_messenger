<template>
    <div>
        <Dropzone
            ref="dz"
            :options="dropzoneOptions"
            @vdropzone-success="onDropzoneSuccess"
        />
    </div>
</template>

<script>
import { Dropzone } from "dropzone-vue";
import "dropzone/dist/dropzone.css";

export default {
    components: {
        Dropzone
    },

    data() {
        return {
            dropzoneOptions: {
                url: "/api/images",
                thumbnailWidth: 150
            }
        };
    },

    methods: {
        onDropzoneSuccess(file, response) {
            this.$emit("new-image", response);
            this.$refs.dz.removeFile(file);
        }
    }
};
</script>
