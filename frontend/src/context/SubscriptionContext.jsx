import {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
} from "react";
import subscriptionService from "../services/subscriptionService";
import { AuthContext } from "./UserContext";

const SubscriptionContext = createContext();

export const useSubscription = () => {
  const context = useContext(SubscriptionContext);
  if (!context) {
    throw new Error("useSubscription must be used within SubscriptionProvider");
  }
  return context;
};

export const SubscriptionProvider = ({ children }) => {
  const { user } = useContext(AuthContext);

  const [subscription, setSubscription] = useState(null);
  const [loading, setLoading] = useState(true);
  const [canCreateShipment, setCanCreateShipment] = useState(false);
  const [cannotCreateReason, setCannotCreateReason] = useState("");
  const [status, setstatus] = useState({});
  console.log(status);
  const fetchSubscriptionData = useCallback(async () => {
    try {
      setLoading(true);
      const response = await subscriptionService.getDashboard();
      if (response.success && response.data) {
        setSubscription(response.data);
        setstatus(response.data.subscription.status);
      } else {
        setSubscription(null);
      }
    } catch (error) {
      console.error("Error fetching subscription:", error);
      setSubscription(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const checkCanCreateShipment = useCallback(async () => {
    try {
      const response = await subscriptionService.checkCanCreateShipment();

      console.log("API Response:", response);

      if (response.success) {
        setCanCreateShipment(response.data.can_create);
        setCannotCreateReason(response.data.reason || "");
      }

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }, []);

  const createCheckout = async (planId) => {
    try {
      const response = await subscriptionService.createCheckout(planId);
      if (response.success) {
        window.location.href = response.data.checkout_url;
      } else {
        throw new Error(
          response.message || "Failed to create checkout session",
        );
      }
      return response;
    } catch (error) {
      console.error("Error creating checkout:", error);
      throw error;
    }
  };

  const cancelSubscription = async () => {
    const response = await subscriptionService.cancelSubscription();
    if (!response.success)
      throw new Error(response.message || "Failed to cancel subscription");
    return response;
  };

  const reactivateSubscription = async () => {
    const response = await subscriptionService.reactivateSubscription();
    if (!response.success)
      throw new Error(response.message || "Failed to reactivate subscription");
    return response;
  };

  const upgradeToPro = async () => {
    const response = await subscriptionService.upgradeToPro();
    if (!response.success)
      throw new Error(response.message || "Failed to upgrade subscription");
    return response;
  };

  const openBillingPortal = async (returnUrl) => {
    try {
      const response = await subscriptionService.openBillingPortal(
        returnUrl || window.location.href,
      );
      if (response.success) {
        window.location.href = response.data.url;
      }
      return response;
    } catch (error) {
      console.error("Error opening billing portal:", error);
      throw error;
    }
  };

  const getSubscriptionStatus = () => subscription?.subscription?.status;

  const isTrialing = () => getSubscriptionStatus() === "trialing";
  const isActive = () => getSubscriptionStatus() === "active";
  const isPastDue = () => getSubscriptionStatus() === "past_due";
  const isCancelled = () => getSubscriptionStatus() === "cancelled";
  const isInactive = () => getSubscriptionStatus() === "inactive";
  const isUnpaid = () => getSubscriptionStatus() === "unpaid";
  const isPaused = () => getSubscriptionStatus() === "paused";
  const isIncomplete = () =>
    ["incomplete", "incomplete_expired"].includes(getSubscriptionStatus());

  const hasAccess = () => {
    const status = getSubscriptionStatus();
    if (status === "active" || status === "trialing") return true;
    if (status === "cancelled" && subscription?.subscription?.ends_at) {
      const endsAt = subscription.subscription.ends_at;
      return endsAt * 1000 > Date.now();
    }
    return false;
  };

  const needsAttention = () => {
    const status = getSubscriptionStatus();
    return [
      "past_due",
      "unpaid",
      "paused",
      "incomplete",
      "incomplete_expired",
    ].includes(status);
  };

  useEffect(() => {
    const init = async () => {
      if (!user) {
        setSubscription(null);
        setCanCreateShipment(false);
        setLoading(false);
        return;
      }

      setLoading(true);
      await Promise.all([fetchSubscriptionData(), checkCanCreateShipment()]);
    };

    init();
  }, [user?.id]);

  const hasActiveSubscription = isActive() || isTrialing() || isCancelled();
  const hasSubscription =
    subscription?.subscription?.status &&
    subscription?.subscription?.status !== "inactive";
  const isPro = subscription?.subscription?.plan === "pro";
  const remainingShipments = subscription?.usage?.shipments_remaining || 0;
  const invoices = subscription?.invoices || [];

  const value = {
    subscription,
    invoices,
    loading,
    hasActiveSubscription,
    hasAccess: hasAccess(),
    needsAttention: needsAttention(),
    isActive: isActive(),
    isTrialing: isTrialing(),
    isPastDue: isPastDue(),
    isCancelled: isCancelled(),
    isInactive: isInactive(),
    isUnpaid: isUnpaid(),
    isPaused: isPaused(),
    isIncomplete: isIncomplete(),
    isPro,
    remainingShipments,
    canCreateShipment,
    cannotCreateReason,
    fetchSubscriptionData,
    createCheckout,
    cancelSubscription,
    reactivateSubscription,
    upgradeToPro,
    openBillingPortal,
    checkCanCreateShipment,
    hasSubscription,
    status,
  };

  return (
    <SubscriptionContext.Provider value={value}>
      {children}
    </SubscriptionContext.Provider>
  );
};
