import { useState, useEffect, useRef, useCallback } from "react";
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  Button,
  Chip,
  Divider,
  Alert,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Snackbar,
  Paper,
  Avatar,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  IconButton,
  Tooltip,
} from "@mui/material";
import {
  CheckCircle as CheckCircleIcon,
  Cancel as CancelIcon,
  Warning as WarningIcon,
  CreditCard as CreditCardIcon,
  TrendingUp as TrendingUpIcon,
  Download as DownloadIcon,
  Refresh as RefreshIcon,
  Payment as PaymentIcon,
  Pause as PauseIcon,
  Schedule as ScheduleIcon,
} from "@mui/icons-material";
import { useSubscription } from "../../context/SubscriptionContext";
import { useNavigate } from "react-router-dom";
import { format } from "date-fns";
import LoadingSpinner from "../../Components/LoadingSpinner";

const formatBillingDate = (timestamp) => {
  if (!timestamp) return "the end of your billing period";
  try {
    let numericTimestamp = timestamp;
    if (typeof timestamp === "string") {
      numericTimestamp = parseInt(timestamp, 10);
    }
    if (
      isNaN(numericTimestamp) ||
      numericTimestamp === 0 ||
      numericTimestamp === null
    ) {
      return "the end of your billing period";
    }
    const date = new Date(numericTimestamp * 1000);
    if (isNaN(date.getTime())) return "the end of your billing period";
    if (
      date.getFullYear() === 1970 &&
      date.getMonth() === 0 &&
      date.getDate() <= 2
    ) {
      return "the end of your billing period";
    }
    return format(date, "MMMM dd, yyyy");
  } catch {
    return "the end of your billing period";
  }
};

const SubscriptionDashboard = () => {
  const navigate = useNavigate();
  const {
    subscription,
    loading,
    cancelSubscription,
    reactivateSubscription,
    upgradeToPro,
    openBillingPortal,
    fetchSubscriptionData,
    hasActiveSubscription,
    isActive,
    isTrialing,
    isPastDue,
    isCancelled,
    isUnpaid,
    isPaused,
    isIncomplete,
    hasAccess,
    needsAttention,
  } = useSubscription();

  const [cancelDialogOpen, setCancelDialogOpen] = useState(false);
  const [reactivateDialogOpen, setReactivateDialogOpen] = useState(false);
  const [upgradeDialogOpen, setUpgradeDialogOpen] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [message, setMessage] = useState({
    open: false,
    text: "",
    severity: "success",
  });

  const hasRedirected = useRef(false);
  const cancelInProgress = useRef(false);
  const reactivateInProgress = useRef(false);
  const upgradeInProgress = useRef(false);

  const invoices = subscription?.invoices || [];

  useEffect(() => {
    if (loading) return;
    if (
      !hasAccess &&
      !subscription?.subscription?.status &&
      !hasRedirected.current
    ) {
      hasRedirected.current = true;
      navigate("/customer/subscription/plans", { replace: true });
    }
  }, [loading, hasAccess, subscription, navigate]);

  const handleRefresh = useCallback(async () => {
    setRefreshing(true);
    await fetchSubscriptionData();
    setRefreshing(false);
  }, [fetchSubscriptionData]);

  const handleCancel = async () => {
    if (cancelInProgress.current) return;
    cancelInProgress.current = true;
    setCancelDialogOpen(false);
    setRefreshing(true);

    try {
      const result = await cancelSubscription();
      setMessage({
        open: true,
        text:
          result?.data?.message ||
          result?.message ||
          "Subscription cancelled. You'll have access until the end of your billing period.",
        severity: "success",
      });
    } catch (error) {
      setMessage({
        open: true,
        text:
          error.response?.data?.message ||
          error.message ||
          "Failed to cancel subscription",
        severity: "error",
      });
    } finally {
      await fetchSubscriptionData();
      setRefreshing(false);
      cancelInProgress.current = false;
    }
  };

  const handleReactivate = async () => {
    if (reactivateInProgress.current) return;
    reactivateInProgress.current = true;
    setReactivateDialogOpen(false);
    setRefreshing(true);

    try {
      await reactivateSubscription();
      setMessage({
        open: true,
        text: "Subscription reactivated successfully!",
        severity: "success",
      });
    } catch (error) {
      setMessage({
        open: true,
        text:
          error.response?.data?.message || "Failed to reactivate subscription",
        severity: "error",
      });
    } finally {
      await fetchSubscriptionData();
      setRefreshing(false);
      reactivateInProgress.current = false;
    }
  };

  const handleUpgrade = async () => {
    if (upgradeInProgress.current) return;

    upgradeInProgress.current = true;
    setUpgradeDialogOpen(false);
    setRefreshing(true);

    try {
      const response = await upgradeToPro();

      if (response.success) {
        setTimeout(async () => {
          await fetchSubscriptionData();
        }, 1000);
      }

      setMessage({
        open: true,
        text: "Upgrade request submitted. Waiting for payment confirmation",
        severity: "success",
      });
    } catch (error) {
      setMessage({
        open: true,
        text: error.response?.data?.message || "Failed to upgrade subscription",
        severity: "error",
      });
    } finally {
      upgradeInProgress.current = false;
      setRefreshing(false);
    }
  };

  const handleBillingPortal = async () => {
    try {
      await openBillingPortal(window.location.href);
    } catch {
      setMessage({
        open: true,
        text: "Failed to open billing portal",
        severity: "error",
      });
    }
  };

  const getStatusConfig = () => {
    const status = subscription?.subscription?.status;
    const configs = {
      active: {
        icon: <CheckCircleIcon />,
        text: "Active",
        color: "success",
        message: "Your subscription is active and you have full access.",
      },
      trialing: {
        icon: <ScheduleIcon />,
        text: "Trial",
        color: "info",
        message: `Your free trial ends on ${formatBillingDate(
          subscription?.subscription?.trial_ends_at,
        )}.`,
      },
      past_due: {
        icon: <WarningIcon />,
        text: "Past Due",
        color: "warning",
        message: "Your payment is past due. Please update your payment method.",
      },
      cancelled: {
        icon: <CancelIcon />,
        text: "Cancelled",
        color: "error",
        message: subscription?.subscription?.ends_at
          ? `Your access continues until ${formatBillingDate(subscription.subscription.ends_at)}`
          : "Your subscription has been cancelled.",
      },
      unpaid: {
        icon: <WarningIcon />,
        text: "Unpaid",
        color: "error",
        message: "All payment attempts have failed. Please resubscribe.",
      },
      paused: {
        icon: <PauseIcon />,
        text: "Paused",
        color: "warning",
        message: "Your subscription is paused. Please add a payment method.",
      },
      incomplete: {
        icon: <WarningIcon />,
        text: "Pending",
        color: "warning",
        message: "Your payment is being confirmed.",
      },
      incomplete_expired: {
        icon: <CancelIcon />,
        text: "Expired",
        color: "error",
        message: "Your payment confirmation window has expired.",
      },
    };
    return configs[status] || configs.active;
  };

  const getActionButtons = () => {
    const status = subscription?.subscription?.status;
    const isProUser = subscription?.subscription?.plan === "pro";

    if (
      status === "unpaid" ||
      status === "paused" ||
      status === "incomplete_expired"
    ) {
      return (
        <Button
          variant="contained"
          onClick={() => navigate("/customer/subscription/plans")}
          size="large"
          fullWidth={false}
        >
          Resubscribe Now
        </Button>
      );
    }

    if (status === "incomplete") {
      return (
        <Button
          variant="outlined"
          onClick={handleBillingPortal}
          size="large"
          fullWidth={false}
        >
          Complete Payment
        </Button>
      );
    }

    if (status === "past_due") {
      return (
        <Button
          variant="contained"
          color="warning"
          onClick={handleBillingPortal}
          size="large"
          fullWidth={false}
        >
          Update Payment Method
        </Button>
      );
    }

    if (status === "active" || status === "trialing") {
      return (
        <>
          {!isProUser && (
            <Button
              variant="outlined"
              onClick={() => setUpgradeDialogOpen(true)}
              size="large"
              fullWidth={false}
            >
              Upgrade to Pro
            </Button>
          )}
          <Button
            variant="outlined"
            onClick={() => setCancelDialogOpen(true)}
            size="large"
            sx={{
              color: "#d32f2f",
              borderColor: "#d32f2f",
              "&:hover": {
                borderColor: "#d32f2f",
                bgcolor: "rgba(211, 47, 47, 0.04)",
              },
            }}
          >
            Cancel Subscription
          </Button>
        </>
      );
    }

    if (status === "cancelled") {
      return (
        <Button
          variant="contained"
          onClick={() => setReactivateDialogOpen(true)}
          size="large"
        >
          Reactivate Subscription
        </Button>
      );
    }

    return null;
  };

  if (loading && !subscription) {
    return <LoadingSpinner />;
  }

  if (refreshing) {
    return <LoadingSpinner message="Updating subscription..." />;
  }

  if (!subscription?.subscription?.status) return null;

  const statusConfig = getStatusConfig();
  const isProUser = subscription?.subscription?.plan === "pro";
  const showUsageCard = isActive || isTrialing;

  return (
    <Container
      maxWidth="lg"
      sx={{ py: { xs: 2, sm: 3, md: 4 }, px: { xs: 2, sm: 3 } }}
    >
      <Box
        sx={{
          mb: { xs: 2, sm: 3, md: 4 },
          display: "flex",
          justifyContent: "space-between",
          alignItems: { xs: "flex-start", sm: "center" },
          flexWrap: "wrap",
          gap: 2,
        }}
      >
        <Typography
          variant="h4"
          component="h1"
          sx={{
            fontWeight: "bold",
            fontSize: { xs: "1.5rem", sm: "2rem", md: "2.125rem" },
          }}
        >
          My Subscription
        </Typography>
        <Box sx={{ display: "flex", gap: 1, alignItems: "center" }}>
          <Tooltip title="Refresh">
            <IconButton onClick={handleRefresh} size="small">
              <RefreshIcon />
            </IconButton>
          </Tooltip>
          <Button
            variant="outlined"
            startIcon={<PaymentIcon />}
            onClick={handleBillingPortal}
            size="small"
            sx={{ whiteSpace: "nowrap" }}
          >
            Manage Payment
          </Button>
        </Box>
      </Box>

      {needsAttention && (
        <Alert
          severity={statusConfig.color}
          sx={{ mb: 3, borderRadius: 2 }}
          action={
            <Button
              color="inherit"
              size="small"
              onClick={
                isPastDue || isPaused || isIncomplete
                  ? handleBillingPortal
                  : () => navigate("/customer/subscription/plans")
              }
            >
              {isPastDue
                ? "Update Now"
                : isPaused
                  ? "Add Payment"
                  : "Resubscribe"}
            </Button>
          }
        >
          <Typography variant="subtitle2" sx={{ fontWeight: "bold" }}>
            {statusConfig.text}
          </Typography>
          <Typography variant="body2">{statusConfig.message}</Typography>
        </Alert>
      )}

      {isTrialing && subscription?.subscription?.trial_ends_at && (
        <Alert
          severity="info"
          sx={{ mb: 3, borderRadius: 2 }}
          icon={<ScheduleIcon />}
        >
          <Typography variant="subtitle2" sx={{ fontWeight: "bold" }}>
            Free Trial Active
          </Typography>
          <Typography variant="body2">
            Your free trial ends on{" "}
            {formatBillingDate(subscription.subscription.trial_ends_at)}
          </Typography>
        </Alert>
      )}

      <Grid container spacing={{ xs: 2, sm: 3 }}>
        <Grid size={{ xs: 12, md: 8 }}>
          <Card sx={{ borderRadius: 3, overflow: "hidden", height: "100%" }}>
            <Box
              sx={{
                bgcolor: "#f5f5f5",
                p: { xs: 1.5, sm: 2 },
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                borderBottom: "1px solid #e0e0e0",
              }}
            >
              <Typography
                variant="h6"
                sx={{
                  fontWeight: "bold",
                  fontSize: { xs: "1rem", sm: "1.25rem" },
                }}
              >
                Current Plan
              </Typography>
              <Chip
                icon={statusConfig.icon}
                label={statusConfig.text}
                size="small"
                color={statusConfig.color}
              />
            </Box>
            <CardContent sx={{ p: { xs: 2, sm: 3 } }}>
              <Box
                sx={{
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  mb: 3,
                  flexWrap: "wrap",
                  gap: 2,
                }}
              >
                <Box>
                  <Typography
                    variant="h3"
                    sx={{
                      fontWeight: "bold",
                      fontSize: { xs: "1.75rem", sm: "2.5rem", md: "3rem" },
                    }}
                  >
                    {subscription?.subscription?.plan_name || "Basic Plan"}
                  </Typography>
                  {isProUser && (
                    <Chip
                      label="PRO"
                      size="small"
                      sx={{ mt: 1, fontWeight: "bold", bgcolor: "#ffd700" }}
                    />
                  )}
                  {isTrialing && (
                    <Chip
                      label="TRIAL"
                      size="small"
                      sx={{
                        mt: 1,
                        fontWeight: "bold",
                        bgcolor: "#2196f3",
                        color: "white",
                      }}
                    />
                  )}
                </Box>
                <Avatar
                  sx={{
                    bgcolor: hasActiveSubscription ? "#1b1b1b" : "#757575",
                    width: { xs: 48, sm: 64 },
                    height: { xs: 48, sm: 64 },
                  }}
                >
                  {isProUser ? (
                    <TrendingUpIcon sx={{ fontSize: { xs: 28, sm: 40 } }} />
                  ) : (
                    <CreditCardIcon sx={{ fontSize: { xs: 28, sm: 40 } }} />
                  )}
                </Avatar>
              </Box>

              <Divider sx={{ my: 2 }} />

              {isCancelled && subscription?.subscription?.ends_at && (
                <Box sx={{ mb: 2 }}>
                  <Typography variant="body2" sx={{ color: "#666" }}>
                    Access Until
                  </Typography>
                  <Typography
                    variant="body1"
                    sx={{ fontWeight: "bold", color: "#d32f2f" }}
                  >
                    {formatBillingDate(subscription.subscription.ends_at)}
                  </Typography>
                </Box>
              )}

              {(isActive || isTrialing) &&
                subscription?.subscription?.current_period_end && (
                  <Box sx={{ mb: 2 }}>
                    <Typography variant="body2" sx={{ color: "#666" }}>
                      Next Billing Date
                    </Typography>
                    <Typography variant="body1" sx={{ fontWeight: "bold" }}>
                      {formatBillingDate(
                        subscription.subscription.current_period_end,
                      )}
                    </Typography>
                  </Box>
                )}

              <Box
                sx={{
                  mt: 3,
                  display: "flex",
                  gap: 2,
                  flexWrap: "wrap",
                  flexDirection: { xs: "column", sm: "row" },
                }}
              >
                {getActionButtons()}
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 4 }}>
          <Card sx={{ borderRadius: 4, height: "100%", boxShadow: 2 }}>
            <CardContent
              sx={{ p: { xs: 2, sm: 3, md: 4 }, textAlign: "center" }}
            >
              <Typography variant="h6" sx={{ fontWeight: 600, mb: 3 }}>
                Monthly Usage
              </Typography>

              <Typography
                variant="h2"
                sx={{
                  fontWeight: 700,
                  fontSize: { xs: "2.5rem", sm: "3.75rem" },
                }}
              >
                {subscription?.usage?.shipments_used || 0}
              </Typography>

              <Typography color="text.secondary">Shipments Used</Typography>

              {!isProUser && (
                <Box sx={{ mt: 4, p: 2, borderRadius: 3, bgcolor: "#f5f5f5" }}>
                  <Typography variant="h4" sx={{ fontWeight: 700 }}>
                    {Math.max(
                      0,
                      (subscription?.usage?.limit || 10) -
                        (subscription?.usage?.shipments_used || 0),
                    )}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Shipments Remaining
                  </Typography>
                </Box>
              )}

              {isProUser && (
                <Box sx={{ mt: 4 }}>
                  <CheckCircleIcon sx={{ color: "#4caf50", fontSize: 50 }} />
                  <Typography sx={{ mt: 1, fontWeight: 600, color: "#4caf50" }}>
                    Unlimited Shipments
                  </Typography>
                </Box>
              )}

              {subscription?.usage?.reset_date > 0 && (
                <Typography
                  variant="caption"
                  display="block"
                  sx={{ mt: 3, color: "text.secondary" }}
                >
                  Resets on{" "}
                  {format(
                    new Date(subscription.usage.reset_date * 1000),
                    "MMM dd, yyyy",
                  )}
                </Typography>
              )}
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {invoices.length > 0 && (
        <Box sx={{ mt: { xs: 3, md: 4 } }}>
          <Typography
            variant="h6"
            gutterBottom
            sx={{
              fontWeight: "bold",
              mb: 2,
              fontSize: { xs: "1rem", sm: "1.25rem" },
            }}
          >
            Billing History
          </Typography>
          <TableContainer
            component={Paper}
            sx={{ borderRadius: 3, overflowX: "auto" }}
          >
            <Table sx={{ minWidth: 400 }}>
              <TableHead sx={{ bgcolor: "#fafafa" }}>
                <TableRow>
                  <TableCell sx={{ fontWeight: "bold", whiteSpace: "nowrap" }}>
                    Invoice #
                  </TableCell>
                  <TableCell sx={{ fontWeight: "bold", whiteSpace: "nowrap" }}>
                    Date
                  </TableCell>
                  <TableCell sx={{ fontWeight: "bold", whiteSpace: "nowrap" }}>
                    Amount
                  </TableCell>
                  <TableCell sx={{ fontWeight: "bold", whiteSpace: "nowrap" }}>
                    Status
                  </TableCell>
                  <TableCell align="center" sx={{ fontWeight: "bold" }}>
                    Actions
                  </TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {invoices.map((invoice) => (
                  <TableRow key={invoice.id} hover>
                    <TableCell>
                      <Typography
                        variant="body2"
                        sx={{
                          fontWeight: "medium",
                          maxWidth: { xs: 80, sm: "none" },
                          overflow: "hidden",
                          textOverflow: "ellipsis",
                          whiteSpace: "nowrap",
                        }}
                      >
                        {invoice.number}
                      </Typography>
                    </TableCell>
                    <TableCell sx={{ whiteSpace: "nowrap" }}>
                      {format(new Date(invoice.created * 1000), "MMM dd, yyyy")}
                    </TableCell>
                    <TableCell>
                      <Typography
                        variant="body2"
                        sx={{ fontWeight: "bold", whiteSpace: "nowrap" }}
                      >
                        ${(invoice.amount_paid / 100).toFixed(2)}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={invoice.status?.toUpperCase() || "UNKNOWN"}
                        size="small"
                        color={
                          invoice.status === "paid"
                            ? "success"
                            : invoice.status === "open"
                              ? "warning"
                              : invoice.status === "uncollectible"
                                ? "error"
                                : "default"
                        }
                      />
                    </TableCell>
                    <TableCell align="center">
                      {invoice.invoice_pdf && (
                        <Tooltip title="Download Invoice">
                          <IconButton
                            href={invoice.invoice_pdf}
                            target="_blank"
                            size="small"
                          >
                            <DownloadIcon />
                          </IconButton>
                        </Tooltip>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Box>
      )}

      <Dialog
        open={cancelDialogOpen}
        onClose={() => setCancelDialogOpen(false)}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Cancel Subscription?</DialogTitle>
        <DialogContent sx={{ mt: 2 }}>
          <DialogContentText>
            Are you sure you want to cancel your{" "}
            {subscription?.subscription?.plan_name || "Basic Plan"}?
          </DialogContentText>
          <Box sx={{ mt: 2, p: 2, bgcolor: "#fff3e0", borderRadius: 2 }}>
            <Typography variant="body2" sx={{ color: "#e65100" }}>
              ⚠️ You'll still have access until{" "}
              {subscription?.subscription?.current_period_end
                ? formatBillingDate(
                    subscription.subscription.current_period_end,
                  )
                : "the end of your billing period"}
            </Typography>
          </Box>
        </DialogContent>
        <DialogActions sx={{ p: 2, flexWrap: "wrap", gap: 1 }}>
          <Button onClick={() => setCancelDialogOpen(false)}>Keep Plan</Button>
          <Button
            onClick={handleCancel}
            variant="contained"
            sx={{ bgcolor: "#d32f2f", "&:hover": { bgcolor: "#c62828" } }}
          >
            Yes, Cancel
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={reactivateDialogOpen}
        onClose={() => setReactivateDialogOpen(false)}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Reactivate Subscription</DialogTitle>
        <DialogContent sx={{ mt: 2 }}>
          <DialogContentText>
            Do you want to reactivate your subscription? You will continue to
            have access to all subscription features.
          </DialogContentText>
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button onClick={() => setReactivateDialogOpen(false)}>Cancel</Button>
          <Button onClick={handleReactivate} variant="contained">
            Reactivate
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={upgradeDialogOpen}
        onClose={() => setUpgradeDialogOpen(false)}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Upgrade to Pro Plan</DialogTitle>
        <DialogContent sx={{ mt: 2 }}>
          <DialogContentText>
            Get unlimited shipments, priority support, and exclusive benefits!
          </DialogContentText>
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button onClick={() => setUpgradeDialogOpen(false)}>Not Now</Button>
          <Button onClick={handleUpgrade} variant="contained">
            Upgrade Now
          </Button>
        </DialogActions>
      </Dialog>

      <Snackbar
        open={message.open}
        autoHideDuration={6000}
        onClose={() => setMessage({ ...message, open: false })}
        anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
      >
        <Alert
          severity={message.severity}
          onClose={() => setMessage({ ...message, open: false })}
        >
          {message.text}
        </Alert>
      </Snackbar>
    </Container>
  );
};

export default SubscriptionDashboard;
