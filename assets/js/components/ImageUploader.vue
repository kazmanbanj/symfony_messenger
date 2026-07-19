<template>
    <div>
        <FilePond
            name="file"
            ref="pond"
            :allow-multiple="true"
            :allow-image-preview="true"
            :max-files="10"
            :server="serverConfig"
            @processfile="onProcessFile"
            @processfileerror="onProcessFileError"
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
            serverConfig: null
        };
    },

    created() {
        // build server config here so we can reference `this` in callbacks
        this.serverConfig = {
            process: {
                url: "/api/images",
                method: "POST",
                onload: response => {
                    try {
                        return JSON.parse(response);
                    } catch (e) {
                        return response;
                    }
                },
                onerror: response => {
                    // Try to parse a helpful message from the server JSON
                    try {
                        const parsed = JSON.parse(response);

                        // Symfony validation normalizer often provides 'detail' or 'violations'
                        if (parsed.detail) {
                            this.$emit('upload-error', parsed.detail);
                            return parsed.detail;
                        }

                        if (Array.isArray(parsed) && parsed.length > 0) {
                            // Fallback: join violation messages
                            const messages = parsed.map(p => p.message || p.title || JSON.stringify(p));
                            const msg = messages.join(', ');
                            this.$emit('upload-error', msg);
                            return msg;
                        }
                    } catch (e) {
                        // not JSON, return raw response
                    }

                    const msg = response || 'Upload error';
                    this.$emit('upload-error', msg);
                    return msg;
                }
            }
        };
    },

    methods: {
        onProcessFile(error, file) {
            if (error) return;

            if (file.serverId && typeof file.serverId === 'object') {
                this.$emit('new-image', file.serverId);
                return;
            }

            if (file.serverId && typeof file.serverId === 'string') {
                try {
                    const response = JSON.parse(file.serverId);
                    this.$emit('new-image', response);
                    return;
                } catch (e) {
                    // fallback to string
                }
            }

            this.$emit('new-image', file.serverId);
        },

        onProcessFileError(error, file) {
            // Called when FilePond reports a processing error for this file
            let msg = 'Upload failed';

            try {
                if (file && file.serverId) {
                    const parsed = JSON.parse(file.serverId);
                    if (parsed && parsed.detail) msg = parsed.detail;
                    else if (Array.isArray(parsed)) msg = parsed.map(p => p.message || p.title || JSON.stringify(p)).join(', ');
                }
            } catch (e) {
                // ignore
            }

            this.$emit('upload-error', msg);
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