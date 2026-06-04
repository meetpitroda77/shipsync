import api from "./axiosInstance";
import echo from "../config/echo";

const notificationService = {
  subscriptions: new Map(),

  getNotifications: async (page = 1, perPage = 15) => {
    const response = await api.get("/notifications", {
      params: { page, per_page: perPage },
    });
    return response.data;
  },

  getUnreadCount: async () => {
    const response = await api.get("/notifications/unread-count");
    return response.data;
  },

  getUnreadNotifications: async (page = 1, perPage = 15) => {
    const response = await api.get("/notifications/unread", {
      params: { page, per_page: perPage },
    });
    return response.data;
  },

  markAsRead: async (notificationId) => {
    const response = await api.post(`/notifications/${notificationId}/read`);
    return response.data;
  },

  markAllAsRead: async () => {
    const response = await api.post("/notifications/mark-all-read");
    return response.data;
  },

  deleteNotification: async (notificationId) => {
    const response = await api.delete(`/notifications/${notificationId}`);
    return response.data;
  },

  deleteAllNotifications: async () => {
    const response = await api.delete("/notifications/delete-all");
    return response.data;
  },

  subscribeToNotifications: (userId, onNotification) => {
    try {
      if (notificationService.subscriptions.has(userId)) {
        return notificationService.subscriptions.get(userId);
      }

      const channel = echo.private(`user.${userId}`);

      

      channel.notification((notification) => {

        const formattedNotification = {
          id: notification.id,
          created_at: notification.time || new Date().toISOString(),
          read_at: null,

          data: {
            message: notification.message,
            type: notification.type,
            shipment_id: notification.shipment_id,
            tracking_id: notification.tracking_id,
          },
        };

        onNotification(formattedNotification);
      });
      notificationService.subscriptions.set(userId, channel);

      return channel;
    } catch (error) {
      console.error(error);
      return null;
    }
  },

  unsubscribeFromNotifications: (userId) => {
    if (notificationService.subscriptions.has(userId)) {
      if (echo) {
        echo.leave(`private-user.${userId}`);
      }

      notificationService.subscriptions.delete(userId);

      console.log(`Unsubscribed user ${userId}`);
    }
  },
};

export default notificationService;
