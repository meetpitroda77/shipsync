import { useContext, useEffect, useRef, useState, useCallback } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { Container, CircularProgress, Typography, Paper } from "@mui/material";
import { AuthContext } from "../../context/UserContext";
import { useSubscription } from "../../context/SubscriptionContext";
import subscriptionService from "../../services/subscriptionService";

const SubscriptionPaymentStatus = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);
  const { fetchSubscriptionData } = useSubscription();

  const [loading, setLoading] = useState(true);

  const done = useRef(false);
  const sessionId = searchParams.get("session_id");
  const plan = searchParams.get("plan");
  const isCancel = window.location.pathname.includes("cancel");

  const redirectToDashboard = useCallback(() => {
    navigate("/customer/subscription/dashboard", { replace: true });
  }, [navigate]);

  const redirectToPlans = useCallback(() => {
    navigate("/customer/subscription/plans", { replace: true });
  }, [navigate]);

  useEffect(() => {
    if (!user?.role) return;

    if (isCancel) {
      toast.info("Subscription cancelled. No charges were made.");
      redirectToPlans();
      return;
    }

    if (!sessionId) {
      toast.error("Invalid session. Please try again.");
      redirectToPlans();
      return;
    }

    done.current = false;

    const poll = async () => {
      if (done.current) return;

      try {
        const response =
          await subscriptionService.getSubscriptionStatus(sessionId);
        if (done.current) return;

        if (response.status === "active" || response.status === "trialing") {
          done.current = true;
          await fetchSubscriptionData();
          toast.success(
            response.status === "trialing"
              ? "Trial started!"
              : "Subscription activated!",
          );
          redirectToDashboard();
          return;
        }

        if (
          ["error", "failed", "expired", "past_due"].includes(response.status)
        ) {
          done.current = true;
          toast.error(response.message || "Payment failed. Please try again.");
          redirectToPlans();
          return;
        }

        setTimeout(poll, 2000);
      } catch {
        if (!done.current) setTimeout(poll, 3000);
      }
    };

    poll();

    return () => {
      done.current = true;
    };
  }, [user?.role]);

  return (
    <Container maxWidth="sm" sx={{ py: 8 }}>
      <Paper sx={{ p: 4, textAlign: "center", borderRadius: 3 }}>
        <CircularProgress size={60} />
        <Typography variant="h6" sx={{ mt: 3 }}>
          Verifying your subscription...
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
          Please wait, this may take a few seconds.
        </Typography>
      </Paper>
    </Container>
  );
};

export default SubscriptionPaymentStatus;
