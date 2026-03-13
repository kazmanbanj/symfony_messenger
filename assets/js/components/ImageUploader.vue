<template>
    <div>
        <FilePond
            name="file"
            ref="pond"
            allow-multiple="true"
            :max-files="10"
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
                    onload: response => response // server returns filename or JSON
                },
                // revert: "/api/images/revert"
            }
        };
    },

    methods: {
        onProcessFile(error, file) {
            if (error) return;

            // FilePond returns serverId as a string
            const response = JSON.parse(file.serverId);

            // Emit each uploaded image to parent
            this.$emit("new-image", response);
        }
    }
};
</script>


<style>
.filepond--credits,
.filepond--credits * {
    display: none !important;
}

</style>