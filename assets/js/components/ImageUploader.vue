<template>
    <div>
        <FilePond
            name="file"
            ref="pond"
            :server="serverConfig"
            @processfile="onProcessFile"
        />
    </div>
</template>

<script>
import vueFilePond from "vue-filepond";
import "filepond/dist/filepond.min.css";

// Optional: image preview plugin
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";

const FilePond = vueFilePond(FilePondPluginImagePreview);

export default {
    components: { FilePond },

    data() {
        return {
            serverConfig: {
                process: {
                    url: "/api/images",
                    method: "POST",
                    onload: response => response // return server response
                }
            }
        };
    },

    methods: {
        onProcessFile(error, file) {
            if (error) return;

            const response = JSON.parse(file.serverId);
            this.$emit("new-image", response);
        }
    }
};
</script>
