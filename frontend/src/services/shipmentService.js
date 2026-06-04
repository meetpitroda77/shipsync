import api from "./axiosInstance";

const shipmentService = {
  getShipments: async (
    page,
    search,
    sortField,
    sortDirection,
    status,
    deliveryMethod,
    startDate,
    endDate,
  ) => {
    const response = await api.get(
      `/shipments?page=${page}
    &search=${search}
    &sort_field=${sortField}
    &sort_direction=${sortDirection}
    &status=${status}
    &delivery_method=${deliveryMethod}
    &start_date=${startDate}
    &end_date=${endDate}`,
    );

    return response.data;
  },
  createShipment: async (formData) => {
    const response = await api.post("/shipments", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  validateShipment: async (formData) => {
    const response = await api.post("/shipments/validate", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  getByIdShipment: async (id) => {
    const response = await api.get(`/shipments/${id}`);
    return response.data;
  },

  deleteShipment: async (id) => {
    const response = await api.delete(`/shipments/${id}`);
    return response.data;
  },
  updateShipmentStatus: async (id, formData) => {
    const response = await api.patch(`/shipments/${id}/status`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },
  getPaymentStatus: async (shipmentId, sessionId) => {
    const response = await api.get(`/shipments/${shipmentId}/payment-status`, {
      params: { session_id: sessionId },
    });
    return response.data;
  },
  getPayments: async (
    page,
    search,
    sortField,
    sortDirection,
    status,
    startDate,
    endDate,
  ) => {
    const response = await api.get("/payments", {
      params: {
        page,
        search,
        sort_field: sortField,
        sort_direction: sortDirection,
        status,
        start_date: startDate,
        end_date: endDate,
      },
    });

    return response.data;
  },
};

export default shipmentService;
