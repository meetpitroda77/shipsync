import { useState, useEffect } from "react";
import {
  Box,
  Container,
  Typography,
  Card,
  CardContent,
  CardActions,
  Button,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  CircularProgress,
  Alert,
  Snackbar,
  Divider,
  useTheme,
} from "@mui/material";
import {
  Check as CheckIcon,
  Rocket as RocketIcon,
  Inventory as InventoryIcon,
  Warning as WarningIcon,
} from "@mui/icons-material";
import { useSubscription } from "../../context/SubscriptionContext";
import { useNavigate } from "react-router-dom";

const PLANS = {
  basic: {
    name: "Basic Plan",
    price: 9.99,
    features: ["10 shipments per month"],
  },
  pro: {
    name: "Pro Plan",
    price: 29,
    features: ["Unlimited shipments"],
  },
};

const SubscriptionPlans = () => {
  const theme = useTheme();
  const navigate = useNavigate();

  const {
    createCheckout,
    loading: contextLoading,

    hasAccess,
    isPastDue,

    isUnpaid,
    isPaused,
    fetchSubscriptionData,
  } = useSubscription();

  const [processingPlan, setProcessingPlan] = useState(null);
  const [error, setError] = useState(null);
  const [redirectChecked, setRedirectChecked] = useState(false);
  const [showWarning, setShowWarning] = useState(false);  

  useEffect(() => {
    const checkAndRedirect = async () => {
      await fetchSubscriptionData();
      if (hasAccess) {
        navigate("/customer/subscription/dashboard", { replace: true });
      } else if (isPastDue || isUnpaid || isPaused) {
        setShowWarning(true);
        setRedirectChecked(true);
      } else {
        setRedirectChecked(true);
      }
    };

    checkAndRedirect();
  }, [
    hasAccess,
    isPastDue,
    isUnpaid,
    isPaused,
    navigate,
    fetchSubscriptionData,
  ]);

  const handleSubscribe = async (planId) => {
    setProcessingPlan(planId);
    try {
      await createCheckout(planId);
    } catch (err) {
      setError(
        err.response?.data?.message || "Failed to create checkout session",
      );
    } finally {
      setProcessingPlan(null);
    }
  };

  const getPlanIcon = (planId) =>
    planId === "pro" ? (
      <RocketIcon
        sx={{
          fontSize: { xs: 44, sm: 56, md: 60 },
          color: theme.palette.secondary.main,
        }}
      />
    ) : (
      <InventoryIcon
        sx={{
          fontSize: { xs: 44, sm: 56, md: 60 },
          color: theme.palette.primary.main,
        }}
      />
    );

  if (contextLoading || !redirectChecked) {
    return (
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          minHeight: "60vh",
        }}
      >
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box
      sx={{
        bgcolor: "grey.50",
        minHeight: "100vh",
        py: { xs: 3, sm: 6, md: 8 },
      }}
    >
      <Container maxWidth="lg" sx={{ px: { xs: 2, sm: 3 } }}>
        <Box sx={{ textAlign: "center", mb: { xs: 3, sm: 5, md: 8 } }}>
          <Typography
            variant="h3"
            component="h1"
            gutterBottom
            fontWeight="bold"
            sx={{
              fontSize: { xs: "1.5rem", sm: "2.25rem", md: "3rem" },
              background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
              backgroundClip: "text",
              WebkitBackgroundClip: "text",
              color: "transparent",
              px: { xs: 1, sm: 2 },
              lineHeight: 1.2,
            }}
          >
            Choose Your Shipping Plan
          </Typography>
          <Typography
            variant="h6"
            color="text.secondary"
            sx={{
              maxWidth: 560,
              mx: "auto",
              fontSize: { xs: "0.9rem", sm: "1.1rem", md: "1.25rem" },
              px: { xs: 1, sm: 2 },
            }}
          >
            Select the perfect plan for your shipping needs. Upgrade, downgrade,
            or cancel anytime.
          </Typography>
        </Box>

        {showWarning && (
          <Alert
            severity="warning"
            icon={<WarningIcon />}
            sx={{ mb: 4, maxWidth: 600, mx: "auto", borderRadius: 2 }}
          >
            <Typography variant="subtitle2" sx={{ fontWeight: "bold" }}>
              {isPastDue && "Payment Past Due"}
              {isUnpaid && "Subscription Cancelled"}
              {isPaused && "Subscription Paused"}
            </Typography>
            <Typography variant="body2">
              {isPastDue &&
                "Please update your payment method to continue your subscription."}
              {isUnpaid &&
                "Your previous subscription has been cancelled. You can resubscribe below."}
              {isPaused &&
                "Your subscription is paused. Please add a payment method to resume."}
            </Typography>
          </Alert>
        )}

        <Box
          sx={{
            display: "flex",
            flexDirection: { xs: "column", md: "row" },
            justifyContent: "center",
            alignItems: { xs: "stretch", md: "stretch" },
            gap: { xs: 3, sm: 3, md: 4 },
            mx: { xs: 0, sm: "auto" },
            maxWidth: { sm: 480, md: "none" },
          }}
        >
          {Object.entries(PLANS).map(([planId, plan]) => (
            <Box
              key={planId}
              sx={{
                flex: { md: "1 1 0" },
                maxWidth: { md: 520 },
                display: "flex",
                mt: planId === "pro" ? { xs: 0, md: 0 } : 0,
              }}
            >
              <Card
                variant="outlined"
                sx={{
                  width: "100%",
                  display: "flex",
                  flexDirection: "column",
                  position: "relative",
                  borderRadius: { xs: 3, sm: 4 },
                  overflow: "visible",
                  boxShadow: "0 1px 4px rgba(0,0,0,0.08)",
                }}
              >
                <CardContent
                  sx={{ flexGrow: 1, p: { xs: 2.5, sm: 3.5, md: 4 } }}
                >
                  <Box sx={{ textAlign: "center", mb: { xs: 1.5, sm: 2.5 } }}>
                    {getPlanIcon(planId)}
                  </Box>

                  <Typography
                    variant="h4"
                    component="h2"
                    gutterBottom
                    fontWeight="bold"
                    color={planId === "pro" ? "secondary" : "primary"}
                    sx={{
                      textAlign: "center",
                      fontSize: { xs: "1.35rem", sm: "1.75rem", md: "2.25rem" },
                    }}
                  >
                    {plan.name}
                  </Typography>

                  <Box sx={{ textAlign: "center", mb: { xs: 2, sm: 3 } }}>
                    <Typography
                      variant="h2"
                      component="span"
                      fontWeight="bold"
                      sx={{
                        fontSize: { xs: "2.25rem", sm: "3rem", md: "3.5rem" },
                      }}
                    >
                      ${plan.price}
                    </Typography>
                    <Typography
                      variant="h6"
                      component="span"
                      color="text.secondary"
                      sx={{ fontSize: { xs: "0.9rem", sm: "1.1rem" } }}
                    >
                      /month
                    </Typography>
                  </Box>

                  <Divider sx={{ my: { xs: 1.5, sm: 2.5 } }} />

                  <Typography
                    variant="subtitle1"
                    fontWeight="bold"
                    gutterBottom
                    sx={{ fontSize: { xs: "0.85rem", sm: "1rem" } }}
                  >
                    What's included:
                  </Typography>
                  <List sx={{ py: 0 }}>
                    {plan.features.map((feature, idx) => (
                      <ListItem key={idx} sx={{ px: 0, py: 0.5 }}>
                        <ListItemIcon sx={{ minWidth: 32 }}>
                          <CheckIcon color="success" fontSize="small" />
                        </ListItemIcon>
                        <ListItemText
                          primary={feature}
                          primaryTypographyProps={{
                            variant: "body2",
                            sx: { fontSize: { xs: "0.85rem", sm: "0.9rem" } },
                          }}
                        />
                      </ListItem>
                    ))}
                  </List>
                </CardContent>

                <CardActions sx={{ p: { xs: 2.5, sm: 3.5, md: 4 }, pt: 0 }}>
                  <Button
                    fullWidth
                    variant="contained"
                    color={planId === "pro" ? "secondary" : "primary"}
                    size="large"
                    onClick={() => {
                      if (processingPlan === null) handleSubscribe(planId);
                    }}
                    disabled={processingPlan === planId}
                    sx={{
                      py: { xs: 1.25, sm: 1.5 },
                      borderRadius: 2,
                      fontSize: { xs: "0.82rem", sm: "0.95rem", md: "1.05rem" },
                      fontWeight: "bold",
                      ...(processingPlan !== null &&
                        processingPlan !== planId && {
                          pointerEvents: "none",
                          opacity: 1,
                        }),
                    }}
                  >
                    {processingPlan === planId ? (
                      <CircularProgress size={22} color="inherit" />
                    ) : (
                      `Subscribe to ${plan.name}`
                    )}
                  </Button>
                </CardActions>
              </Card>
            </Box>
          ))}
        </Box>

        <Box
          sx={{
            display: "flex",
            flexDirection: { xs: "column", sm: "row" },
            justifyContent: "center",
            alignItems: "center",
            gap: { xs: 1, sm: 2 },
            mt: { xs: 3, sm: 5 },
            flexWrap: "wrap",
            px: 2,
          }}
        >
          <Typography
            variant="body2"
            color="text.secondary"
            sx={{
              textAlign: "center",
              fontSize: { xs: "0.8rem", sm: "0.875rem" },
            }}
          >
            🔒 Secure payment powered by Stripe
          </Typography>
          <Typography
            variant="body2"
            color="text.secondary"
            sx={{
              textAlign: "center",
              fontSize: { xs: "0.8rem", sm: "0.875rem" },
            }}
          >
            💳 Cancel anytime • No hidden fees
          </Typography>
        </Box>

        <Snackbar
          open={!!error}
          autoHideDuration={6000}
          onClose={() => setError(null)}
          anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
        >
          <Alert
            severity="error"
            onClose={() => setError(null)}
            sx={{ width: "100%" }}
          >
            {error}
          </Alert>
        </Snackbar>
      </Container>
    </Box>
  );
};

export default SubscriptionPlans;
