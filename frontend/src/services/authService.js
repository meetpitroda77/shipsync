import api from "./axiosInstance";

const authService = {
  register: async (data) => {
    const response = await api.post("/register", data);
    return response.data;
  },

  login: async (data) => {
    console.log("Sending login data:", data);
    const response = await api.post("/login", data);
    return response.data;
  },
  forgotPassword: async (email) => {
    const response = await api.post("/forgot-password", {
      email,
    });

    return response.data;
  },

  resetPassword: async (data) => {
    const response = await api.post("/reset-password", data);
    return response.data;
  },

  logout: async () => {
    const response = await api.post("/logout");
    return response.data;
  },
  getUser: async () => {
    const response = await api.get("/profile");
    return response.data;
  },
  verifyEmail: async (id) => {
    const response = await api.get(`/email/verify/${id}`);
    return response.data;
  },
  Updateprofile: async (data) => {
    const response = await api.patch("/profile/update", data);
    return response.data;
  },
};

export default authService;
