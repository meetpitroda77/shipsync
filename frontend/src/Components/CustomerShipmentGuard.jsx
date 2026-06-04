import { Navigate, Outlet } from "react-router-dom";
import { useSubscription } from "../context/SubscriptionContext";
import LoadingSpinner from "./LoadingSpinner";

const CustomerShipmentGuard = () => {
  const {
    loading,
    status,
  } = useSubscription();

  const allowedStatuses = ["active", "trialing"];

  if (loading) {
    return <LoadingSpinner />;
  }

  if (!allowedStatuses.includes(status)) {
    return (
      <Navigate
        to="/customer/subscription/dashboard"
        replace
        state={{
          message: `Cannot create shipment. Subscription status: ${status}`,
        }}
      />
    );
  }

  return <Outlet />;
};

export default CustomerShipmentGuard;