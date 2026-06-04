import api from "./axiosInstance";

const dashboardService = {
  getDataDashboard: async (formData) => {
    const response = await api.get("/dashboard", formData);
    return response.data;
  },
};

export default dashboardService;
