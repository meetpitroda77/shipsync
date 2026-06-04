import api from "./axiosInstance";

const userService = {
  getUser: async (
    page,
    search,
    sortField,
    sortDirection,
    role,
    startDate,
    endDate,
  ) => {
    const response = await api.get("/users", {
      params: {
        page,
        search,
        sort_field: sortField,
        sort_direction: sortDirection,
        role,
        start_date: startDate,
        end_date: endDate,
      },
    });

    return response.data;
  },
  deleteUser: async (id) => {
    const response = await api.delete(`users/${id}`);
    return response.data;
  },
  updateRole: async (id, formData) => {
    const response = await api.patch(`users/${id}/role`, formData);
    return response.data;
  },
  createUser: async (formData) => {
    const response = await api.post("/users", formData);
    return response.data;
  },
};

export default userService;
