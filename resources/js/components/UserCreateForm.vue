<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const token = ref("");

const form = ref({
    name: "",
    email: "",
    phone: "",
    position_id: "",
    photo: null,
});

const positions = ref([]);

const errors = ref({});
const conflictError = ref("");
const loading = ref(false);

async function submitForm() {
    try {

        loading.value = true;
        errors.value = {};
        conflictError.value = "";

        const formData = new FormData();
        Object.entries(form.value).forEach(([key, value]) => {
            if (key === 'phone') {
                formData.append(key, `+380${value}`);
            } else {
                formData.append(key, value);
            }
        });

        const response = await axios.post("/api/v1/users", formData, {
            headers: {
                Token: token.value,
            },
        });
        console.log("User created:", response.data);
        alert("User successfully created!");
    } catch (error) {
        const { status, data } = error.response || {};

        if (status === 401 || status === 409) {
            conflictError.value = data.message;
        } else if (error.response?.status === 422) {
            errors.value = data.fails || {};
        } else {
            console.error("Unexpected error:", error);
        }
    } finally {
        loading.value = false;
        getToken();
    }
}

async function fetchPositions() {
    try {
        const response = await axios.get("/api/v1/positions");
        positions.value = response.data.positions;
    } catch (error) {
        console.error("Failed to fetch positions:", error);
    }
}

function clearError(key) {
    if (errors.value[key]) {
        delete errors.value[key];
    }
}

async function getToken() {
    try {
        const response = await axios.get("/api/v1/token");
        token.value = response.data.token;
    } catch (error) {
        console.error("Failed to get token:", error);
    }
}

onMounted(() => {
    fetchPositions();
    getToken();
});
</script>

<template>
    <div class="d-flex justify-content-center align-items-center mt-5">
        <div class="card p-4" style="width: 400px">
            <h4 class="text-center mb-4">Create User</h4>
            <form @submit.prevent="submitForm">
                <div v-if="conflictError" class="alert alert-danger">
                    {{ conflictError }}
                </div>


                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" v-model="form.name" @change="clearError('name')" class="form-control"
                        :class="{ 'is-invalid': errors.name }" />
                    <div class="invalid-feedback" v-if="errors.name">
                        {{ errors.name.join(", ") }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" id="email" v-model="form.email" @change="clearError('email')"
                        class="form-control" :class="{ 'is-invalid': errors.email }" />
                    <div class="invalid-feedback" v-if="errors.email">
                        {{ errors.email.join(", ") }}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">+380</span>
                        <input type="text" id="phone" v-model="form.phone" @change="clearError('phone')"
                            class="form-control" placeholder="XXXXXXXXX" :class="{ 'is-invalid': errors.phone }"
                            maxlength="9" />
                        <div class="invalid-feedback" v-if="errors.phone">
                            {{ errors.phone.join(", ") }}
                        </div>
                    </div>

                </div>

                <div class="mb-3">
                    <label for="position_id" class="form-label">Position</label>
                    <select id="position_id" v-model="form.position_id" @change="clearError('position_id')"
                        class="form-select" :class="{ 'is-invalid': errors.position_id }">
                        <option value="" disabled>Select a position</option>
                        <option v-for="position in positions" s :key="position.id" :value="position.id">
                            {{ position.name }}
                        </option>
                    </select>
                    <div class="invalid-feedback" v-if="errors.position_id">
                        {{ errors.position_id.join(", ") }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Photo</label>
                    <input type="file" id="photo" accept=".jpeg,.jpg" @change="(e) => {
                        form.photo = e.target.files[0];
                        clearError('photo');
                    }
                        " class="form-control" :class="{ 'is-invalid': errors.photo }" />
                    <div class="invalid-feedback" v-if="errors.photo">
                        {{ errors.photo.join(", ") }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="token" class="form-label">Token</label>
                    <input type="text" id="token" v-model="token" disabled class="form-control" />
                    <small class="form-text text-muted">
                        The token is required for each request and is generated automatically.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                    {{ loading ? "Submitting..." : "Create" }}
                </button>
            </form>
        </div>
    </div>
</template>
