import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import router from './router';
import App from './App.vue';

// Global Components
import FormInput from './components/common/FormInput.vue';
import FormDropdown from './components/common/FormDropdown.vue';
import ImageUploader from './components/common/ImageUploader.vue';

const app = createApp(App);

app.component('FormInput', FormInput);
app.component('FormDropdown', FormDropdown);
app.component('ImageUploader', ImageUploader);

app.use(router);
app.mount('#app');