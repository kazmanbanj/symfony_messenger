import { createApp } from 'vue';
import ImageApp from './components/ImageApp.vue';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap';

const app = createApp({});

// Register your component
app.component('image-app', ImageApp);

// Mount the app
app.mount('#images-app');
