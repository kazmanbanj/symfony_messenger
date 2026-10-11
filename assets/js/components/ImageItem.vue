<template>
    <li :class="{ deleting: isDeleting }" class="text-white pt-3">
        <div class="d-flex flex-row">
            <div>
                <a :href="absoluteUrl(url)" target="_blank">
                    <img
                        v-if="!loadFailed"
                        :src="absoluteUrl(url)"
                        :alt="originalFilename"
                        @error="onImgError"
                        @load="onImgLoad"
                    >
                    <div v-else style="width:100px; height:60px; background:#ddd; display:flex; align-items:center; justify-content:center; color:#666; font-size:12px;">
                        preview unavailable
                    </div>
                </a>
            </div>

            <div class="pl-2">
                <span v-if="ponkaAddedAt">
                    Ponka visited your photo {{ ponkaAddedAgo }}
                </span>
                <span v-else>
                    Ponka is napping. Check back soon.
                </span>
            </div>

            <div class="pl-2">
                <button
                    title="clear image"
                    @click="onDeleteClick"
                    class="btn btn-warning font-weight-bold"
                >
                    X
                </button>
            </div>
        </div>
    </li>
</template>

<script>
import moment from 'moment';
import $ from 'jquery';

export default {
    props: ['url', 'originalFilename', 'ponkaAddedAt'],

    data() {
        return {
            isDeleting: false,
            ponkaAddedAgo: null,
            timer: null,
            loadFailed: false
        };
    },

    watch: {
        ponkaAddedAt() {
            this.updatePonkaAddedAgo();
        }
    },

    methods: {
        onDeleteClick() {
            this.$emit('delete-image');
            this.isDeleting = true;
        },

        updatePonkaAddedAgo() {
            this.ponkaAddedAgo = moment(this.ponkaAddedAt).fromNow();
        },
        onImgError() {
            console.error('Image failed to load:', this.url, this.absoluteUrl(this.url));
            this.loadFailed = true;
        },
        onImgLoad() {
            this.loadFailed = false;
        },
        absoluteUrl(url) {
            if (!url) return url;
            if (url.startsWith('http://') || url.startsWith('https://')) return url;
            const path = url.startsWith('/') ? url : '/' + url;
            return window.location.origin + encodeURI(path);
        }
    },

    created() {
        this.updatePonkaAddedAgo();
        this.timer = setInterval(this.updatePonkaAddedAgo, 60000);
    },

    beforeUnmount() {
        clearInterval(this.timer);
        $(this.$el).find('button').tooltip('dispose');
    },

    mounted() {
        $(this.$el).find('button').tooltip();
    }
};
</script>

<style scoped lang="scss">
img {
    width: 100px;
    border-radius: 5px;
}
.deleting {
    opacity: .3;
}
button {
    cursor: pointer;
}
</style>
