<template>
    <div>
        <div style="position:relative;">
            <div class="row no-gutters" style="box-shadow: 0 3px 7px 1px rgba(0,0,0,0.06);">
                <div class="col py-5">
                    <h1 class="text-center">Ponka-fy Me</h1>
                </div>
            </div>

            <div class="row no-gutters">
                <div class="col-xs-12 col-md-6 px-5" style="background-color: #659dbd; padding-bottom: 150px;">
                    <h2 class="text-center mb-5 pt-5 text-white">First: Upload Photo</h2>
                    <image-uploader @new-image="onNewUploadedImage" />
                </div>

                <div class="col-xs-12 col-md-6 px-5" style="background-color: #7FB7D7; min-height: 40rem; padding-bottom: 150px;">
                    <h2 class="text-center mb-5 pt-5 text-white">Second: Download Improved Photo</h2>
                    <image-list
                        :images="images"
                        @delete-image="onDeleteImage"
                    />
                </div>
            </div>

            <footer class="footer">
                <p class="text-muted my-5 text-center">
                    Made with love by the
                    <a style="text-decoration: underline; color: #6c757d; font-weight: bold;" href="http://www.symfonycasts.com">
                        SymfonyCasts
                    </a>
                    Team
                </p>
            </footer>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import imageList from './ImageList.vue';
import imageUploader from './ImageUploader.vue';

export default {
    components: {
        imageList,
        imageUploader
    },

    data() {
        return {
            images: []
        };
    },

    methods: {
        onNewUploadedImage(payload) {
            const newImages = Array.isArray(payload) ? payload : [payload];

            newImages.forEach(image => {
                // avoid duplicates
                if (!this.images.find(i => i.id === image.id)) {
                    this.images.unshift(image);
                }
            });
        },

        onDeleteImage(image) {
            axios.delete(image['@id']).then(() => {
                const index = this.images.indexOf(image);
                if (index !== -1) {
                    this.images.splice(index, 1);
                }
            });
        },

        fetchImagesData() {
            axios.get('/api/images')
                .then(response => {
                    this.images = response.data.items;
                });
        }
    },

    mounted() {
        this.fetchImagesData();
    }
};
</script>

<style scoped lang="scss">
.footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    min-height: 60px;
    background-color: #f5f5f5;
}
</style>
