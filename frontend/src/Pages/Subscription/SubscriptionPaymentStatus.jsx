import { useContext, useEffect, useRef, useState, useCallback } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import {
  Container,
  CircularProgress,
  Typography,
  Button,
  Paper,
} from "@mui/material";
import {
  CheckCircle,
  Error as ErrorIcon,
  Cancel as CancelIcon,
  Warning as WarningIcon,
  Schedule as ScheduleIcon,
} from "@mui/icons-material";
import { AuthContext } from "../../context/UserContext";
import { useSubscription } from "../../context/SubscriptionContext";
import subscriptionService from "../../services/subscriptionService";

const SubscriptionPaymentStatus = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);
  const { fetchSubscriptionData } = useSubscription();

  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState(null);
  const [errorMessage, setErrorMessage] = useState("");

  const done = useRef(false);
  const sessionId = searchParams.get("session_id");
  const plan = searchParams.get("plan");
  const isSuccess = window.location.pathname.includes("success");
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
      setLoading(false);
      setStatus("cancelled");
      toast.info("Subscription cancelled. No charges were made.");
      return;
    }

    if (!sessionId) {
      setLoading(false);
      setStatus("error");
      setErrorMessage("Invalid session. Please try again.");
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
          setStatus(response.status);
          setLoading(false);
          await fetchSubscriptionData();
          toast.success(
            response.status === "trialing"
              ? "Trial started!"
              : "Subscription activated!",
          );
          setTimeout(redirectToDashboard, 2000);
          return;
        }

        if (
          ["error", "failed", "expired", "past_due"].includes(response.status)
        ) {
          done.current = true;
          setStatus(response.status === "past_due" ? "past_due" : "error");
          setErrorMessage(
            response.message || "Payment failed. Please try again.",
          );
          setLoading(false);
          toast.error("Payment failed. Please try again.");
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

  if (loading) {
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
  }

  const renderContent = () => {
    if (status === "active")
      return (
        <>
          <CheckCircle sx={{ fontSize: 80, color: "success.main", mb: 2 }} />
          <Typography variant="h4" gutterBottom color="success.main">
            Subscription Active!
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 3 }}>
            You are now on the{" "}
            <strong>{plan === "pro" ? "Pro Plan" : "Basic Plan"}</strong>.
            Redirecting...
          </Typography>
          <Button variant="contained" onClick={redirectToDashboard}>
            Go to Dashboard
          </Button>
        </>
      );

    if (status === "trialing")
      return (
        <>
          <ScheduleIcon sx={{ fontSize: 80, color: "info.main", mb: 2 }} />
          <Typography variant="h4" gutterBottom color="info.main">
            Trial Started!
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 3 }}>
            Your free trial of the{" "}
            <strong>{plan === "pro" ? "Pro Plan" : "Basic Plan"}</strong> has
            started. Redirecting...
          </Typography>
          <Button variant="contained" onClick={redirectToDashboard}>
            Go to Dashboard
          </Button>
        </>
      );

    if (status === "cancelled")
      return (
        <>
          <CancelIcon sx={{ fontSize: 80, color: "warning.main", mb: 2 }} />
          <Typography variant="h4" gutterBottom>
            Cancelled
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 3 }}>
            No charges were made.
          </Typography>
          <Button variant="contained" onClick={redirectToPlans}>
            View Plans
          </Button>
        </>
      );

    if (status === "past_due")
      return (
        <>
          <WarningIcon sx={{ fontSize: 80, color: "warning.main", mb: 2 }} />
          <Typography variant="h4" gutterBottom color="warning.main">
            Payment Failed
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 3 }}>
            {errorMessage || "Please update your payment method and try again."}
          </Typography>
          <Button variant="contained" onClick={redirectToPlans}>
            Try Again
          </Button>
        </>
      );

    return (
      <>
        <ErrorIcon sx={{ fontSize: 80, color: "error.main", mb: 2 }} />
        <Typography variant="h4" gutterBottom color="error.main">
          Something Went Wrong
        </Typography>
        <Typography color="text.secondary" sx={{ mb: 3 }}>
          {errorMessage ||
            "There was an issue processing your subscription. Please try again."}
        </Typography>
        <Button variant="contained" onClick={redirectToPlans}>
          Try Again
        </Button>
      </>
    );
  };

  return (
    <Container maxWidth="sm" sx={{ py: 8 }}>
      <Paper sx={{ p: 4, textAlign: "center", borderRadius: 3 }}>
        {renderContent()}
      </Paper>
    </Container>
  );
};

export default SubscriptionPaymentStatus;
