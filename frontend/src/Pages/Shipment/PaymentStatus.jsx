import { useContext, useEffect, useRef, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { Container, Box, CircularProgress, Typography } from "@mui/material";
import shipmentService from "../../services/shipmentService";
import { AuthContext } from "../../context/UserContext";

const PaymentStatus = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const { user } = useContext(AuthContext);

  const [loading, setLoading] = useState(true);

  const hasCompleted = useRef(false);

  const shipmentId = searchParams.get("shipment_id");
  const sessionId = searchParams.get("session_id");

  const redirectToPage = (role) => {
    console.log("Redirecting with role:", role);
    if (role === "customer") {
      navigate("/customer/shipments", { replace: true });
    } else if (role === "admin") {
      navigate("/admin/shipments", { replace: true });
    } else if (role === "agent") {
      navigate("/agent/shipments", { replace: true });
    } else if (role === "staff") {
      navigate("/staff/shipments", { replace: true });
    } else {
      navigate("/", { replace: true });
    }
  };

  useEffect(() => {
    if (!user?.role) return;

    const checkStatus = async (retries = 10) => {
      if (hasCompleted.current) return;

      try {
        const response = await shipmentService.getPaymentStatus(
          shipmentId,
          sessionId,
        );

        if (response.status === "paid") {
          hasCompleted.current = true;

          setLoading(false);

          toast.success("Payment successful! Your shipment has been booked.");

          redirectToPage(user.role);

          return;
        }
        if (response.status === "pending") {
          hasCompleted.current = true;

          setLoading(false);

          toast.success("payment was cancelled.");

          redirectToPage(user.role);

          return;
        }

        if (response.status === "failed") {
          hasCompleted.current = true;

          setLoading(false);

          toast.error("Payment failed. Please try again.");

          redirectToPage(user.role);

          return;
        }

        if (retries > 0) {
          setTimeout(() => {
            checkStatus(retries - 1);
          }, 2000);
        } else {
          hasCompleted.current = true;

          setLoading(false);

          toast.info("Payment is pending");

          redirectToPage(user.role);
        }
      } catch (error) {
        console.error(error);

        hasCompleted.current = true;

        setLoading(false);

        toast.error("Something went wrong");

        redirectToPage(user.role);
      }
    };

    if (!shipmentId || !sessionId) {
      toast.error("Payment was canceled");

      redirectToPage(user.role);

      return;
    }

    checkStatus();
  }, [shipmentId, sessionId, user]);

  return (
    <Container maxWidth="sm" sx={{ py: 8 }}>
      <Box sx={{ textAlign: "center" }}>
        {loading && (
          <>
            <CircularProgress size={60} />

            <Typography variant="h6" sx={{ mt: 3 }}>
              Verifying your payment...
            </Typography>

            <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
              Please wait while we confirm your transaction
            </Typography>
          </>
        )}
      </Box>
    </Container>
  );
};

export default PaymentStatus;
