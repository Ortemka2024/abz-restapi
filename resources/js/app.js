import './bootstrap';
import { createApp } from 'vue';

const app = createApp({});

import UsersTable from './components/UsersTable.vue';
import UserCreateForm from './components/UserCreateForm.vue';

app.component('users-table', UsersTable);
app.component('user-create-form', UserCreateForm);

app.mount('#app');
