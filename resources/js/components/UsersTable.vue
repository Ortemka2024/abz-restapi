<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const count = 6;
const page = ref(0);
const lastPage = ref(false);
const isLoading = ref(true);

const users = ref([]);

async function loadMoreUsers() {
    try {
        isLoading.value = false;

        page.value += 1

        const response = await axios.get(`/api/v1/users?count=${count}&page=${page.value}`);

        if (response.data.total_pages == page.value) {
            lastPage.value = true;
        }

        // added current page users to list
        // users.value = [...users.value, ...response.data.users];

        // display current page users
        users.value = response.data.users;

        isLoading.value = false

    } catch {
        console.log('error')
    }
};

const hasUsers = computed(() => users.value.length > 0);

onMounted(loadMoreUsers);
</script>

<template>
    <div class="d-flex justify-content-center align-items-center mt-5">
        <div class="container">
            <h3 class="mb-4 text-center">Users table</h3>
            <div v-if="isLoading" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div v-else>
                <div v-if="!hasUsers" class="text-center">
                    <p>No users found.</p>
                </div>
                <table v-else class="table table-striped table-bordered text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Position ID</th>
                            <th>Registration date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="align-middle">
                            <td>{{ user.id }}</td>
                            <td>
                                <img v-if="user.photo" :src="user.photo" alt="User photo" class="img-thumbnail"
                                    style="max-width: 100px;" />
                                <span v-else>Немає фото</span>
                            </td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.phone }}</td>
                            <td>{{ user.position }}</td>
                            <td>{{ user.position_id }}</td>
                            <td>{{ new Date(user.registration_timestamp * 1000).toLocaleDateString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <button @click="loadMoreUsers" :disabled="lastPage" class="btn btn-lg btn-primary">
                    Show more
                </button>
            </div>
        </div>
    </div>
</template>
