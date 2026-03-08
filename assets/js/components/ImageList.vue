<template>
    <div>
        <transition-group name="list" tag="ul">
            <image-item
                v-for="image in images"
                :key="image.id"
                :url="image.url"
                :original-filename="image.originalFilename"
                :ponka-added-at="image.ponkaAddedAt"
                @delete-image="onDeleteImage(image)"
            />
        </transition-group>
    </div>
</template>

<script>
import imageItem from "./ImageItem.vue";

export default {
    components: { imageItem },
    props: ["images"],

    methods: {
        onDeleteImage(image) {
            this.$emit("delete-image", image);
        }
    }
};
</script>
