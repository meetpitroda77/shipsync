import { useState, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import {
  Container,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TableFooter,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Button,
  Box,
  Card,
  CardContent,
  Grid,
  Chip,
  Alert,
  Stack,
  Pagination,
  TextField,
} from "@mui/material";

import {
  FilterList as FilterIcon,
  Clear as ClearIcon,
  PictureAsPdf as PdfIcon,
  TableChart as ExcelIcon,
  FilePresent as CsvIcon,
} from "@mui/icons-material";

import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";

import dayjs from "dayjs";
import api from "../../services/axiosInstance";
import LoadingSpinner from "../../Components/LoadingSpinner";

const Report = () => {
  const [searchParams, setSearchParams] = useSearchParams();

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const [reportData, setReportData] = useState({
    report: [],
    totals: {
      weight: 0,
      subtotal: 0,
      insurance: 0,
      tax: 0,
      total: 0,
    },
    filters: {
      start_date: "",
      end_date: "",
      status: "",
      delivery_method: "",
    },
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
    },
  });

  const [filters, setFilters] = useState({
    status: searchParams.get("status") || "",
    delivery_method: searchParams.get("delivery_method") || "",
    from: searchParams.get("from") || "",
    to: searchParams.get("to") || "",
  });

  const [appliedFilters, setAppliedFilters] = useState({
    status: searchParams.get("status") || "",
    delivery_method: searchParams.get("delivery_method") || "",
    from: searchParams.get("from") || "",
    to: searchParams.get("to") || "",
  });
  const [page, setPage] = useState(() => {
    const pageParam = searchParams.get("page");
    return pageParam ? parseInt(pageParam) : 1;
  });

  const statusOptions = [
    { value: "delivered", label: "Delivered" },
    { value: "in_transit", label: "In Transit" },
    { value: "pending_assigned", label: "Pending Assigned" },
    { value: "pending_payment", label: "Pending Payment" },
    { value: "assigned", label: "Assigned" },
    { value: "picked_up", label: "Picked Up" },
    { value: "out_for_delivery", label: "Out for Delivery" },
    { value: "failed_delivery", label: "Failed Delivery" },
    { value: "delayed", label: "Delayed" },
    { value: "canceled", label: "Canceled" },
  ];

  const deliveryMethodOptions = [
    { value: "standard", label: "Standard" },
    { value: "express", label: "Express" },
  ];

  const updateUrlParams = (newFilters, newPage) => {
    const params = {};

    if (newFilters.status) {
      params.status = newFilters.status;
    }

    if (newFilters.delivery_method) {
      params.delivery_method = newFilters.delivery_method;
    }

    if (newFilters.from) {
      params.from = newFilters.from;
    }

    if (newFilters.to) {
      params.to = newFilters.to;
    }

    if (newPage > 1) {
      params.page = newPage;
    }

    setSearchParams(params, { replace: true });
  };

  const fetchReport = async () => {
    setLoading(true);
    setError(null);

    try {
      const params = {};

      if (appliedFilters.status) {
        params.status = appliedFilters.status;
      }

      if (appliedFilters.delivery_method) {
        params.delivery_method = appliedFilters.delivery_method;
      }

      if (appliedFilters.from) {
        params.from = dayjs(appliedFilters.from).format("YYYY-MM-DD");
      }

      if (appliedFilters.to) {
        params.to = dayjs(appliedFilters.to).format("YYYY-MM-DD");
      }

      params.page = page;

      const response = await api.get("/report-general", { params });

      if (response.data.success) {
        setReportData(response.data.data);
      } else {
        setError("Failed to fetch report data");
      }
    } catch (err) {
      console.error("Error fetching report:", err);
      setError(err.response?.data?.message || "Failed to fetch report data");
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async (format) => {
    setLoading(true);

    try {
      const params = {};

      if (appliedFilters.status) {
        params.status = appliedFilters.status;
      }

      if (appliedFilters.delivery_method) {
        params.delivery_method = appliedFilters.delivery_method;
      }

      if (appliedFilters.from) {
        params.from = dayjs(appliedFilters.from).format("YYYY-MM-DD");
      }

      if (appliedFilters.to) {
        params.to = dayjs(appliedFilters.to).format("YYYY-MM-DD");
      }

      let url = "";
      let filename = "";

      switch (format) {
        case "pdf":
          url = "/report-general/pdf";
          filename = `shipping-report-${dayjs().format(
            "YYYY-MM-DD-HHmmss",
          )}.pdf`;
          break;

        case "excel":
          url = "/report-general/excel";
          filename = `shipping-report-${dayjs().format(
            "YYYY-MM-DD-HHmmss",
          )}.xlsx`;
          break;

        case "csv":
          url = "/report-general/csv";
          filename = `shipping-report-${dayjs().format(
            "YYYY-MM-DD-HHmmss",
          )}.csv`;
          break;

        default:
          return;
      }

      const response = await api.get(url, {
        params,
        responseType: "blob",
      });

      const blob = new Blob([response.data]);

      const downloadUrl = window.URL.createObjectURL(blob);

      const link = document.createElement("a");

      link.href = downloadUrl;
      link.setAttribute("download", filename);

      document.body.appendChild(link);

      link.click();

      link.remove();

      window.URL.revokeObjectURL(downloadUrl);
    } catch (err) {
      console.error(`Error exporting ${format}:`, err);
      setError(`Failed to export ${format.toUpperCase()} file`);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (field, value) => {
    setFilters((prev) => ({
      ...prev,
      [field]: value,
    }));
  };

  const handleApplyFilters = () => {
    setPage(1);
    setAppliedFilters(filters);
    updateUrlParams(filters, 1);
  };

  const handleClearFilters = () => {
    const clearedFilters = {
      status: "",
      delivery_method: "",
      from: null,
      to: null,
    };

    setFilters(clearedFilters);
    setAppliedFilters(clearedFilters);
    setPage(1);

    updateUrlParams(clearedFilters, 1);
  };

  const handlePageChange = (event, value) => {
    setPage(value);
    updateUrlParams(appliedFilters, value);
  };

  useEffect(() => {
    fetchReport();
  }, [page, appliedFilters]);

  const getStatusColor = (status) => {
    const colors = {
      delivered: "success",
      in_transit: "primary",
      pending_assigned: "warning",
      pending_payment: "warning",
      assigned: "info",
      picked_up: "info",
      out_for_delivery: "info",
      failed_delivery: "error",
      delayed: "error",
      canceled: "default",
    };

    return colors[status] || "default";
  };

  return (
    <Container maxWidth="xl" sx={{ mt: 4, mb: 4 }}>
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <LocalizationProvider dateAdapter={AdapterDayjs}>
            <Grid container spacing={2} alignItems="flex-end">
              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <FormControl size="small" fullWidth>
                  <InputLabel>Shipment Status</InputLabel>

                  <Select
                    value={filters.status}
                    label="Shipment Status"
                    onChange={(e) =>
                      handleFilterChange("status", e.target.value)
                    }
                  >
                    <MenuItem value="">All Status</MenuItem>

                    {statusOptions.map((option) => (
                      <MenuItem key={option.value} value={option.value}>
                        {option.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <FormControl size="small" fullWidth>
                  <InputLabel>Delivery Method</InputLabel>

                  <Select
                    value={filters.delivery_method}
                    label="Delivery Method"
                    onChange={(e) =>
                      handleFilterChange("delivery_method", e.target.value)
                    }
                  >
                    <MenuItem value="">All Methods</MenuItem>

                    {deliveryMethodOptions.map((option) => (
                      <MenuItem key={option.value} value={option.value}>
                        {option.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <TextField
                  label="Start Date"
                  type="date"
                  size="small"
                  fullWidth
                  value={filters.from || ""}
                  onChange={(e) => handleFilterChange("from", e.target.value)}
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <TextField
                  label="End Date"
                  type="date"
                  size="small"
                  fullWidth
                  value={filters.to || ""}
                  onChange={(e) => handleFilterChange("to", e.target.value)}
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <Stack direction="row" spacing={1}>
                  <Button
                    variant="contained"
                    startIcon={<FilterIcon />}
                    onClick={handleApplyFilters}
                    fullWidth
                  >
                    Filter
                  </Button>

                  <Button
                    variant="outlined"
                    startIcon={<ClearIcon />}
                    onClick={handleClearFilters}
                    fullWidth
                  >
                    Clear
                  </Button>
                </Stack>
              </Grid>
            </Grid>
          </LocalizationProvider>
        </CardContent>
      </Card>

      <Box
        sx={{
          display: "flex",
          justifyContent: "flex-end",
          gap: 2,
          mb: 2,
        }}
      >
        <Button
          variant="contained"
          color="error"
          startIcon={<PdfIcon />}
          onClick={() => handleExport("pdf")}
          disabled={loading}
        >
          PDF
        </Button>

        <Button
          variant="contained"
          color="success"
          startIcon={<ExcelIcon />}
          onClick={() => handleExport("excel")}
          disabled={loading}
        >
          Excel
        </Button>

        <Button
          variant="contained"
          color="info"
          startIcon={<CsvIcon />}
          onClick={() => handleExport("csv")}
          disabled={loading}
        >
          CSV
        </Button>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setError(null)}>
          {error}
        </Alert>
      )}

      {loading && <LoadingSpinner />}

      {!loading && (
        <>
          <TableContainer component={Paper}>
            <Table sx={{ minWidth: 650 }}>
              <TableHead>
                <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
                  <TableCell>
                    <strong>Tracking ID</strong>
                  </TableCell>

                  <TableCell>
                    <strong>Date</strong>
                  </TableCell>

                  <TableCell>
                    <strong>Sender</strong>
                  </TableCell>

                  <TableCell>
                    <strong>Origin</strong>
                  </TableCell>

                  <TableCell>
                    <strong>Status</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>Weight</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>Subtotal</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>Insurance</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>Tax</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>Total</strong>
                  </TableCell>
                </TableRow>
              </TableHead>

              <TableBody>
                {reportData.report.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={10} align="center">
                      No shipments found
                    </TableCell>
                  </TableRow>
                ) : (
                  reportData.report.map((shipment, index) => (
                    <TableRow key={index} hover>
                      <TableCell>{shipment.tracking}</TableCell>

                      <TableCell>{shipment.date}</TableCell>

                      <TableCell>{shipment.sender}</TableCell>

                      <TableCell>{shipment.origin}</TableCell>

                      <TableCell>
                        <Chip
                          label={
                            shipment.status?.replace(/_/g, " ").toUpperCase() ||
                            ""
                          }
                          color={getStatusColor(shipment.status)}
                          size="small"
                        />
                      </TableCell>

                      <TableCell align="right">{shipment.weight}</TableCell>

                      <TableCell align="right">${shipment.subtotal}</TableCell>

                      <TableCell align="right">${shipment.insurance}</TableCell>

                      <TableCell align="right">${shipment.tax}</TableCell>

                      <TableCell align="right">${shipment.total}</TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>

              <TableFooter>
                <TableRow
                  sx={{
                    backgroundColor: "#f5f5f5",
                    fontWeight: "bold",
                  }}
                >
                  <TableCell colSpan={5} align="center">
                    <strong>Total</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>{reportData.totals.weight}</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>${reportData.totals.subtotal}</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>${reportData.totals.insurance}</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>${reportData.totals.tax}</strong>
                  </TableCell>

                  <TableCell align="right">
                    <strong>${reportData.totals.total}</strong>
                  </TableCell>
                </TableRow>
              </TableFooter>
            </Table>
          </TableContainer>

          {reportData.report.length > 0 && (
            <Box
              sx={{
                display: "flex",
                justifyContent: "center",
                mt: 3,
              }}
            >
              <Pagination
                count={reportData.pagination.last_page || 1}
                page={page}
                onChange={handlePageChange}
                color="primary"
                size="large"
              />
            </Box>
          )}
        </>
      )}
    </Container>
  );
};

export default Report;
