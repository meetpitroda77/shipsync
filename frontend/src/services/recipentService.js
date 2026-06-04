import api from "./axiosInstance";

const recipentService = {
  getRecipents: async (page, search, sortField, sortDirection) => {
    const response = await api.get(
      `/recipients?page=${page}
    &search=${search}
    &sort_field=${sortField}
    &sort_direction=${sortDirection}
  `,
    );

    return response.data;
  },
  createRecipent: async (formData) => {
    const response = await api.post("/recipients", formData);
    return response.data;
  },

  getByIdRecipent: async (id) => {
    const response = await api.get(`/recipients/${id}/edit`);
    return response.data;
  },

  deleteRecipent: async (id) => {
    const response = await api.delete(`/recipients/${id}`);
    return response.data;
  },
  updateRecipent: async (id, formData) => {
    const response = await api.put(`/recipients/${id}`, formData);
    return response.data;
  },
  getAllRecipientsWithAddresses: async () => {
    const response = await api.get("/recipients/all-with-addresses");
    return response.data;
  },
};

export default recipentService;
