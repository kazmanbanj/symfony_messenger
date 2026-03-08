<template>
    <li :class="{ deleting: isDeleting }" class="text-white pt-3">
        <div class="d-flex flex-row">
            <div>
                <a :href="url" target="_blank">
                    <img
                        :src="url"
                        :alt="originalFilename"
                    >
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
            timer: null
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
        }
    },

    created() {
        this.updatePonkaAddedAgo();
        this.timer = setInterval(this.updatePonkaAddedAgo, 60000);
    },

    beforeUnmount() {
        clearInterval(this.timer);
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
