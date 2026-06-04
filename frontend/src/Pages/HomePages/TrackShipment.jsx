import { useState } from "react";
import {
  Container,
  Grid,
  Card,
  CardHeader,
  CardContent,
  TextField,
  Button,
  Box,
  Typography,
  Alert,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  CircularProgress,
} from "@mui/material";
import {
  Check as CheckIcon,
  Inventory as BoxIcon,
  HourglassEmpty as HourglassSplitIcon,
  CreditCard as CreditCardIcon,
  Autorenew as ArrowRepeatIcon,
  AssignmentInd as PersonCheckIcon,
  LocalShipping as BoxSeamIcon,
  LocalShipping as TruckIcon,
  LocalShipping as TruckFlatbedIcon,
  CheckCircle as CheckCircleIcon,
  Cancel as XCircleIcon,
  Schedule as ClockHistoryIcon,
  Block as SlashCircleIcon,
} from "@mui/icons-material";
import api from "../../services/axiosInstance";

const TrackShipment = () => {
  const [trackingId, setTrackingId] = useState("");
  const [shipment, setShipment] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  const getStatusSteps = (shipmentData) => {
    if (!shipmentData) return [];

    const status = shipmentData.status;
    const logStatuses = shipmentData.logs?.map((log) => log.status) || [];

    const allStatuses = {
      created: "Created",
    };

    if (
      ["pending_assigned", "pending_payment", "processing_payment"].includes(
        status,
      )
    ) {
      const statusLabels = {
        pending_assigned: "Pending Assigned",
        pending_payment: "Pending Payment",
        processing_payment: "Processing Payment",
      };
      allStatuses[status] = statusLabels[status];
    }

    if (logStatuses.includes("processing_payment")) {
      allStatuses.processing_payment = "Processing Payment";
    }

    if (logStatuses.includes("assigned")) {
      allStatuses.assigned = "Assigned";
    }

    if (logStatuses.includes("picked_up")) {
      allStatuses.picked_up = "Picked Up";
    }

    if (logStatuses.includes("in_transit")) {
      allStatuses.in_transit = "In Transit";
    }

    if (logStatuses.includes("out_for_delivery")) {
      allStatuses.out_for_delivery = "Out for Delivery";
    }

    let finalStatus = "delivered";
    if (["failed_delivery", "delayed", "canceled"].includes(status)) {
      finalStatus = status;
    }

    const finalStatusLabels = {
      delivered: "Delivered",
      failed_delivery: "Failed Delivery",
      delayed: "Delayed",
      canceled: "Canceled",
    };
    allStatuses[finalStatus] = finalStatusLabels[finalStatus];

    return Object.keys(allStatuses);
  };

  const getCurrentIndex = (shipmentData, steps) => {
    if (!shipmentData) return -1;
    const status = shipmentData.status;
    return steps.indexOf(status);
  };

  const getStepIcon = (stepKey, isCompleted, isActive) => {
    if (isCompleted) {
      return <CheckIcon sx={{ fontSize: 20 }} />;
    }

    const icons = {
      created: <BoxIcon sx={{ fontSize: 20 }} />,
      pending_assigned: <HourglassSplitIcon sx={{ fontSize: 20 }} />,
      pending_payment: <CreditCardIcon sx={{ fontSize: 20 }} />,
      processing_payment: <ArrowRepeatIcon sx={{ fontSize: 20 }} />,
      assigned: <PersonCheckIcon sx={{ fontSize: 20 }} />,
      picked_up: <BoxSeamIcon sx={{ fontSize: 20 }} />,
      in_transit: <TruckIcon sx={{ fontSize: 20 }} />,
      out_for_delivery: <TruckFlatbedIcon sx={{ fontSize: 20 }} />,
      delivered: <CheckCircleIcon sx={{ fontSize: 20 }} />,
      failed_delivery: <XCircleIcon sx={{ fontSize: 20 }} />,
      delayed: <ClockHistoryIcon sx={{ fontSize: 20 }} />,
      canceled: <SlashCircleIcon sx={{ fontSize: 20 }} />,
    };

    return icons[stepKey] || <BoxIcon sx={{ fontSize: 20 }} />;
  };

  const getStepColor = (isCompleted, isActive) => {
    if (isCompleted) return "#28a745";
    if (isActive) return "#0d6efd";
    return "#e0e0e0";
  };

  const handleTrackShipment = async (e) => {
    e.preventDefault();

    const trimmedTrackingId = trackingId.trim();
    if (!trimmedTrackingId) {
      setError("Please enter a tracking ID");
      return;
    }

    setLoading(true);
    setError(null);
    setShipment(null);

    try {
      const response = await api.post(`/track-shipment`, {
        tracking_id: trimmedTrackingId,
      });

      if (response.data.success && response.data.data) {
        setShipment(response.data.data);
        setError(null);
      } else {
        setError(response.data.message || "Shipment not found");
      }
    } catch (err) {
      if (err.response) {
        const { status, data } = err.response;
        if (status === 422) {
          setError(data.message || "Invalid tracking number format");
        } else if (status === 404) {
          setError("Shipment not found. Please check the tracking ID.");
        } else if (status === 401) {
          setError("Please login to track shipments");
        } else if (status === 403) {
          setError("You don't have permission to track this shipment");
        } else {
          setError(
            data.message || "Failed to track shipment. Please try again.",
          );
        }
      } else if (err.request) {
        setError(
          "Cannot connect to server. Please check your internet connection.",
        );
      } else {
        setError("An unexpected error occurred. Please try again.");
      }
    } finally {
      setLoading(false);
    }
  };

  const renderShipmentDetails = () => {
    if (!shipment) return null;

    const steps = getStatusSteps(shipment);
    const currentIndex = getCurrentIndex(shipment, steps);
    const status = shipment.status;
    const failedLog = shipment.logs?.find(
      (log) => log.status === "failed_delivery",
    );

    const getStatusColor = (status) => {
      switch (status) {
        case "delivered":
          return "success";
        case "failed_delivery":
          return "error";
        case "delayed":
          return "warning";
        case "canceled":
          return "error";
        default:
          return "primary";
      }
    };

    const statusLabels = {
      created: "Created",
      pending_assigned: "Pending Assigned",
      pending_payment: "Pending Payment",
      processing_payment: "Processing Payment",
      assigned: "Assigned",
      picked_up: "Picked Up",
      in_transit: "In Transit",
      out_for_delivery: "Out for Delivery",
      delivered: "Delivered",
      failed_delivery: "Failed Delivery",
      delayed: "Delayed",
      canceled: "Canceled",
    };

    return (
      <Card sx={{ mt: 4 }}>
        <CardHeader title={`Shipment Details: ${shipment.tracking_id}`} />
        <CardContent>
          <Box
            sx={{
              width: "100%",
              mb: 4,
              overflowX: "auto",
              position: "relative",
              py: 3,
            }}
          >
            <Box
              sx={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                position: "relative",
                minWidth: "600px",
              }}
            >
              {steps.map((stepKey, idx) => {
                const isCompleted =
                  idx < currentIndex ||
                  (stepKey === "delivered" && status === "delivered");
                const isActive = idx === currentIndex;
                const label = statusLabels[stepKey] || stepKey;

                return (
                  <Box
                    key={stepKey}
                    sx={{
                      flex: 1,
                      textAlign: "center",
                      position: "relative",
                      zIndex: 2,
                    }}
                  >
                    {idx !== steps.length - 1 && (
                      <Box
                        sx={{
                          position: "absolute",
                          top: 20,
                          left: "50%",
                          width: "100%",
                          height: 2,
                          backgroundColor: isCompleted ? "#28a745" : "#e0e0e0",
                          zIndex: 1,
                        }}
                      />
                    )}
                    <Box
                      sx={{
                        width: 40,
                        height: 40,
                        borderRadius: "50%",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        margin: "0 auto",
                        backgroundColor: getStepColor(isCompleted, isActive),
                        color: "white",
                        position: "relative",
                        zIndex: 3,
                        border: isActive ? "3px solid #0d6efd" : "none",
                        boxShadow: isActive
                          ? "0 0 0 3px rgba(13,110,253,0.3)"
                          : "none",
                      }}
                    >
                      {getStepIcon(stepKey, isCompleted, isActive)}
                    </Box>
                    <Typography
                      variant="caption"
                      sx={{
                        mt: 1,
                        fontSize: "12px",
                        display: "block",
                        fontWeight: isActive ? "bold" : "normal",
                        color: isActive
                          ? "#0d6efd"
                          : isCompleted
                            ? "#28a745"
                            : "#666",
                      }}
                    >
                      {label}
                    </Typography>
                  </Box>
                );
              })}
            </Box>
          </Box>

          {shipment.status === "failed_delivery" && (
            <Alert severity="info" sx={{ mb: 3 }}>
              <Typography variant="subtitle1" fontWeight="bold">
                Failed Delivery
              </Typography>
              <Typography variant="body2">
                <strong>Reason:</strong>{" "}
                {failedLog?.description || "No reason provided"}
              </Typography>
            </Alert>
          )}

          <Grid container spacing={3}>
            <Grid size={{ xs: 12, md: 4 }}>
              <Typography variant="h6" gutterBottom>
                Sender Information
              </Typography>
              <Box component="ul" sx={{ listStyle: "none", p: 0, m: 0 }}>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Name:</strong> {shipment.sender_name}
                </Typography>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Phone:</strong> {shipment.sender_phone}
                </Typography>
                <Typography component="li" variant="body2">
                  <strong>Address:</strong>{" "}
                  {shipment.sender_address?.address || "N/A"},
                  {shipment.sender_address?.city || ""}{" "}
                  {shipment.sender_address?.state || ""}{" "}
                  {shipment.sender_address?.country || ""}{" "}
                  {shipment.sender_address?.zip_code || ""}
                </Typography>
              </Box>
            </Grid>

            <Grid size={{ xs: 12, md: 4 }}>
              <Typography variant="h6" gutterBottom>
                Receiver Information
              </Typography>
              <Box component="ul" sx={{ listStyle: "none", p: 0, m: 0 }}>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Name:</strong> {shipment.receiver_name}
                </Typography>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Phone:</strong> {shipment.receiver_phone}
                </Typography>
                <Typography component="li" variant="body2">
                  <strong>Address:</strong>{" "}
                  {shipment.receiver_address?.address || "N/A"},
                  {shipment.receiver_address?.city || ""}{" "}
                  {shipment.receiver_address?.state || ""}{" "}
                  {shipment.receiver_address?.country || ""}{" "}
                  {shipment.receiver_address?.zip_code || ""}
                </Typography>
              </Box>
            </Grid>

            <Grid size={{ xs: 12, md: 4 }}>
              <Typography variant="h6" gutterBottom>
                Status & Delivery
              </Typography>
              <Box component="ul" sx={{ listStyle: "none", p: 0, m: 0 }}>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Status:</strong>{" "}
                  <Chip
                    label={statusLabels[shipment.status] || shipment.status}
                    size="small"
                    color={getStatusColor(shipment.status)}
                  />
                </Typography>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Delivery Method:</strong> {shipment.delivery_method}
                </Typography>
                <Typography component="li" variant="body2" gutterBottom>
                  <strong>Estimated Delivery:</strong>{" "}
                  {shipment.estimated_delivery_date}
                </Typography>
                <Typography component="li" variant="body2">
                  <strong>Actual Delivery:</strong>{" "}
                  {shipment.actual_delivery_date || "N/A"}
                </Typography>
              </Box>
            </Grid>
          </Grid>

          <Box sx={{ mt: 4 }}>
            <Typography variant="h6" gutterBottom>
              Package Information
            </Typography>
            {shipment.packages && shipment.packages.length > 0 ? (
              <TableContainer component={Paper} variant="outlined">
                <Table size="small">
                  <TableHead>
                    <TableRow>
                      <TableCell>Description</TableCell>
                      <TableCell align="right">Weight (kg)</TableCell>
                      <TableCell align="right">
                        Dimensions (L x W x H)
                      </TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {shipment.packages.map((pkg, idx) => (
                      <TableRow key={idx}>
                        <TableCell>{pkg.description}</TableCell>
                        <TableCell align="right">{pkg.weight} kg</TableCell>
                        <TableCell align="right">
                          {pkg.length} x {pkg.width} x {pkg.height} cm
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            ) : (
              <Typography variant="body2" color="text.secondary">
                No package information available.
              </Typography>
            )}
          </Box>

          <Box sx={{ mt: 4 }}>
            <Typography variant="h6" gutterBottom>
              Invoice
            </Typography>
            {shipment.invoice ? (
              <Button
                variant="contained"
                href={`/shipment/invoice/${shipment.id}`}
                target="_blank"
              >
                Download Invoice
              </Button>
            ) : (
              <Typography variant="body2" color="text.secondary">
                No invoice generated for this shipment yet.
              </Typography>
            )}
          </Box>
        </CardContent>
      </Card>
    );
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 3, mb: 4 }}>
      <Grid container justifyContent="center">
        <Grid size={{ xs: 12 }}>
          <Card>
            <CardHeader title="Track Your Shipment" />
            <CardContent>
              <form onSubmit={handleTrackShipment}>
                <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                  <TextField
                    fullWidth
                    variant="outlined"
                    placeholder="Enter tracking ID"
                    value={trackingId}
                    onChange={(e) => setTrackingId(e.target.value)}
                    disabled={loading}
                    size="small"
                    sx={{ flex: 1, minWidth: "200px" }}
                  />
                  <Button
                    type="submit"
                    variant="contained"
                    disabled={loading}
                    sx={{ minWidth: "100px" }}
                  >
                    {loading ? <CircularProgress size={24} /> : "Track"}
                  </Button>
                </Box>
              </form>
            </CardContent>
          </Card>

          {error && (
            <Alert severity="error" sx={{ mt: 4 }}>
              {error}
            </Alert>
          )}

          {loading && (
            <Box sx={{ display: "flex", justifyContent: "center", mt: 4 }}>
              <CircularProgress />
            </Box>
          )}

          {renderShipmentDetails()}
        </Grid>
      </Grid>
    </Container>
  );
};

export default TrackShipment;
