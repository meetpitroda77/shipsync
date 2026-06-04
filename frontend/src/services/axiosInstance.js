import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost:8000/api",
  timeout: 100000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true,
});

api.interceptors.request.use(
  (config) => {
    const token = JSON.parse(localStorage.getItem("token"));

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    console.log("Request Sent to:", config.url);
    console.log("Request Headers:", config.headers);
    
    if (config.data instanceof FormData) {
      console.log("FormData being sent");
      for (let pair of config.data.entries()) {
        console.log(pair[0] + ": " + (pair[1] instanceof File ? pair[1].name : pair[1]));
      }
    }

    return config;
  },
  (error) => Promise.reject(error),
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");

      if (window.location.pathname !== "/login") {
        window.location.href = "/login";
      }
    }

    return Promise.reject(error);
  }
);

export default api;