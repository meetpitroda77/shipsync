import api from "./axiosInstance";

const settingsService = {
  getServices: async () => {
    const res = await api.get("/settings");
    return res.data;
  },
  createServices: async (data) => {
    const res = await api.post("/settings", data);
    return res.data;
  },
  updateServices: async (id, data) => {
    const res = await api.put(`/settings/${id}`, data);
    return res.data;
  },
  deleteService: async (id) => {
    const res = await api.delete(`/settings/${id}`);
    return res.data;
  },
};
export default settingsService;
