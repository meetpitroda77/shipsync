import api from "./axiosInstance";

const subscriptionService = {
  getDashboard: async () => {
    const response = await api.get("/subscription/dashboard");
    return response.data;
  },

  createCheckout: async (planId) => {
    const response = await api.post("/subscription/checkout", {
      plan: planId,
    });
    return response.data;
  },

  cancelSubscription: async () => {
    const response = await api.post("/subscription/cancel");
    return response.data;
  },

  reactivateSubscription: async () => {
    const response = await api.post("/subscription/reactivate");
    return response.data;
  },

  upgradeToPro: async () => {
    const response = await api.post("/subscription/upgrade-to-pro");
    return response.data;
  },

  openBillingPortal: async (returnUrl) => {
    const response = await api.post("/subscription/billing-portal", {
      return_url: returnUrl,
    });
    return response.data;
  },

  checkCanCreateShipment: async () => {
    const response = await api.get("/subscription/can-create-shipment");
    return response.data;
  },
  getSubscriptionStatus: async (sessionId) => {
    const response = await api.get("/subscription/status/check", {
      params: { session_id: sessionId },
    });
    return response.data;
  },
};

export default subscriptionService;
