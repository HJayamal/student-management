import axios from "axios";

const studentApi = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

// Keep the student's token separate from the teacher's token.
studentApi.interceptors.request.use((config) => {
    const token = localStorage.getItem("student_token");

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

export default studentApi;
