import { useParams } from "react-router-dom";
import {
  Container,
  Grid,
  Card,
  CardHeader,
  CardContent,
  Typography,
  Box,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Button,
  Chip,
  Divider,
  Stack,
  Alert,
} from "@mui/material";
import { Download } from "@mui/icons-material";
import { useState, useEffect } from "react";
import shipmentService from "../../services/shipmentService";
import LoadingSpinner from "../../Components/LoadingSpinner";
import api from "../../services/axiosInstance";
import { toast } from "react-toastify";

const ShipmentDetails = () => {
  const { id } = useParams();
  const [shipment, setShipment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchShipmentDetails = async (id) => {
      try {
        setLoading(true);
        const response = await shipmentService.getByIdShipment(id);

        setShipment(response.data);
        setError(null);
      } catch (err) {
        setError("Failed to load shipment details");
        console.error("Error fetching shipment:", err);
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchShipmentDetails(id);
    }
  }, [id]);

  const handleDownloadInvoice = async () => {
    try {
      const response = await api.get(`/shipments/${id}/invoice`, {
        responseType: "blob",
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `invoice_${shipment.tracking_id}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error("Error downloading invoice:", err);
      if (err.response?.status === 404) {
        toast.error("Invoice not found for this shipment");
      } else {
        toast.error("Failed to download invoice. Please try again.");
      }
    }
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error) {
    return (
      <Container maxWidth="lg" sx={{ mt: 4, px: { xs: 2, sm: 3, md: 4 } }}>
        <Alert severity="error">{error}</Alert>
      </Container>
    );
  }

  if (!shipment) {
    return (
      <Container maxWidth="lg" sx={{ mt: 4, px: { xs: 2, sm: 3, md: 4 } }}>
        <Alert severity="warning">Shipment not found</Alert>
      </Container>
    );
  }

  return (
    <Container
      maxWidth="xl"
      sx={{ py: { xs: 2, sm: 3, md: 4 }, px: { xs: 2, sm: 3, md: 4 } }}
    >
      <Card sx={{ borderRadius: { xs: 1, sm: 2 } }}>
        <CardHeader
          title={
            <Typography
              variant="h5"
              component="h1"
              sx={{
                fontSize: { xs: "1.25rem", sm: "1.5rem", md: "1.75rem" },
              }}
            >
              Shipment Details: {shipment.tracking_id?.toUpperCase()}
            </Typography>
          }
          sx={{ pb: { xs: 1, sm: 2 } }}
        />
        <CardContent sx={{ p: { xs: 2, sm: 3 } }}>
          <Grid container spacing={{ xs: 2, sm: 3 }}>
            <Grid size={{ xs: 12, md: 4 }}>
              <Paper
                elevation={0}
                sx={{
                  p: { xs: 1.5, sm: 2 },
                  bgcolor: "background.default",
                  height: "100%",
                }}
              >
                <Typography
                  variant="h6"
                  gutterBottom
                  color="primary"
                  sx={{ fontSize: { xs: "1rem", sm: "1.25rem" } }}
                >
                  Sender Information
                </Typography>
                <Stack spacing={1}>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Name:</strong> {shipment.sender_name}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Phone:</strong> {shipment.sender_phone}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Address:</strong>{" "}
                    {shipment.sender_address?.address || "N/A"},{" "}
                    {shipment.sender_address?.city || ""}{" "}
                    {shipment.sender_address?.state || ""}{" "}
                    {shipment.sender_address?.country || ""}{" "}
                    {shipment.sender_address?.zip_code || ""}
                  </Typography>
                </Stack>
              </Paper>
            </Grid>

            <Grid size={{ xs: 12, md: 4 }}>
              <Paper
                elevation={0}
                sx={{
                  p: { xs: 1.5, sm: 2 },
                  bgcolor: "background.default",
                  height: "100%",
                }}
              >
                <Typography
                  variant="h6"
                  gutterBottom
                  color="primary"
                  sx={{ fontSize: { xs: "1rem", sm: "1.25rem" } }}
                >
                  Receiver Information
                </Typography>
                <Stack spacing={1}>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Name:</strong> {shipment.receiver_name}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Phone:</strong> {shipment.receiver_phone}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Address:</strong>{" "}
                    {shipment.receiver_address?.address || "N/A"},{" "}
                    {shipment.receiver_address?.city || ""}{" "}
                    {shipment.receiver_address?.state || ""}{" "}
                    {shipment.receiver_address?.country || ""}{" "}
                    {shipment.receiver_address?.zip_code || ""}
                  </Typography>
                </Stack>
              </Paper>
            </Grid>

            <Grid size={{ xs: 12, md: 4 }}>
              <Paper
                elevation={0}
                sx={{
                  p: { xs: 1.5, sm: 2 },
                  bgcolor: "background.default",
                  height: "100%",
                }}
              >
                <Typography
                  variant="h6"
                  gutterBottom
                  color="primary"
                  sx={{ fontSize: { xs: "1rem", sm: "1.25rem" } }}
                >
                  Status & Delivery
                </Typography>
                <Stack spacing={1}>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Status:</strong>{" "}
                    <Chip
                      label={
                        shipment.status?.charAt(0).toUpperCase() +
                        shipment.status?.slice(1)
                      }
                      color={
                        shipment.status === "delivered"
                          ? "success"
                          : shipment.status === "in_transit"
                            ? "warning"
                            : "default"
                      }
                      size="small"
                      sx={{ fontSize: { xs: "0.7rem", sm: "0.8rem" } }}
                    />
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Delivery Method:</strong> {shipment.delivery_method}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Estimated Delivery:</strong>{" "}
                    {shipment.estimated_delivery_date}
                  </Typography>
                  <Typography
                    variant="body2"
                    sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                  >
                    <strong>Actual Delivery:</strong>{" "}
                    {shipment.actual_delivery_date || "N/A"}
                  </Typography>
                </Stack>
              </Paper>
            </Grid>
          </Grid>

          <Divider sx={{ my: { xs: 2, sm: 3 } }} />

          <Box sx={{ mb: { xs: 2, sm: 3 } }}>
            <Typography
              variant="h6"
              gutterBottom
              color="primary"
              sx={{ fontSize: { xs: "1rem", sm: "1.25rem" } }}
            >
              Package Information
            </Typography>
            {shipment.packages && shipment.packages.length > 0 ? (
              <TableContainer
                component={Paper}
                elevation={1}
                sx={{ overflowX: "auto" }}
              >
                <Table sx={{ minWidth: { xs: 300, sm: "auto" } }}>
                  <TableHead>
                    <TableRow sx={{ bgcolor: "background.default" }}>
                      <TableCell
                        align="center"
                        sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                      >
                        Description
                      </TableCell>
                      <TableCell
                        align="center"
                        sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                      >
                        Weight (kg)
                      </TableCell>
                      <TableCell
                        align="center"
                        sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                      >
                        Dimensions (L x W x H)
                      </TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {shipment.packages.map((pkg, index) => (
                      <TableRow key={index}>
                        <TableCell
                          align="center"
                          sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                        >
                          {pkg.description}
                        </TableCell>
                        <TableCell
                          align="center"
                          sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                        >
                          {pkg.weight} kg
                        </TableCell>
                        <TableCell
                          align="center"
                          sx={{ fontSize: { xs: "0.75rem", sm: "0.875rem" } }}
                        >
                          {pkg.length} x {pkg.width} x {pkg.height} cm
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            ) : (
              <Alert severity="info">No package information available.</Alert>
            )}
          </Box>

          <Divider sx={{ my: { xs: 2, sm: 3 } }} />

          <Box sx={{ mb: { xs: 2, sm: 3 } }}>
            <Typography
              variant="h6"
              gutterBottom
              color="primary"
              sx={{ fontSize: { xs: "1rem", sm: "1.25rem" } }}
            >
              Invoice
            </Typography>
            {shipment.invoice ? (
              <Button
                variant="contained"
                startIcon={<Download />}
                onClick={handleDownloadInvoice}
                sx={{
                  textTransform: "none",
                  fontSize: { xs: "0.75rem", sm: "0.875rem" },
                  py: { xs: 0.5, sm: 1 },
                }}
              >
                Download Invoice
              </Button>
            ) : (
              <Alert severity="info">
                No invoice generated for this shipment yet.
              </Alert>
            )}
          </Box>

          <Divider sx={{ my: { xs: 2, sm: 3 } }} />

          <Box>
            <Typography variant="h6" gutterBottom color="primary">
              Shipment Images
            </Typography>
            {shipment.images && shipment.images.length > 0 ? (
              <Grid container spacing={{ xs: 1, sm: 2 }}>
                {shipment.images.map((image, index) => (
                  <Grid size={{ xs: 12, sm: 6, md: 4, lg: 3 }} key={index}>
                    <Paper elevation={2} sx={{ overflow: "hidden" }}>
                      <img
                        src={image.full_url}
                        alt={`Shipment ${index + 1}`}
                        style={{
                          width: "100%",
                          height: 200,
                          display: "block",
                          objectFit: "cover",
                          cursor: "pointer",
                        }}
                        onError={(e) => {
                          console.error(
                            "Failed to load image:",
                            image.full_url,
                          );
                          e.target.style.display = "none";
                          e.target.parentElement.innerHTML = `
                  <div style="padding: 20px; text-align: center;">
                    <p>Failed to load image</p>
                    <small>${image.image_path}</small>
                  </div>
                `;
                        }}
                      />
                    </Paper>
                  </Grid>
                ))}
              </Grid>
            ) : (
              <Alert severity="info">
                No images available for this shipment.
              </Alert>
            )}
          </Box>
        </CardContent>
      </Card>
    </Container>
  );
};

export default ShipmentDetails;
