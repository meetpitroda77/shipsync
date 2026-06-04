import { useState, useEffect } from "react";
import {
  Container,
  Typography,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Button,
  Box,
  CircularProgress,
  Alert,
  Pagination,
  Stack,
  Card,
  CardContent,
  Grid,
} from "@mui/material";
import {
  FilterAlt as FilterIcon,
  Clear as ClearIcon,
} from "@mui/icons-material";
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import api from "../../services/axiosInstance";

const DailyReport = () => {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    start_date: "",
    end_date: "",
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });

  const fetchReports = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        page: pagination.current_page,
      });

      if (filters.start_date) {
        params.append("start_date", filters.start_date);
      }
      if (filters.end_date) {
        params.append("end_date", filters.end_date);
      }

      const response = await api.get(`/reports?${params.toString()}`);

      if (response.data.success) {
        setReports(response.data.data.data);
        setPagination({
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          per_page: response.data.data.per_page,
          total: response.data.data.total,
        });
      } else {
        toast.error("Failed to fetch reports");
      }
    } catch (error) {
      console.error("Error fetching reports:", error);
      toast.error("Error loading reports. Please try again.");
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => {
    fetchReports();
  }, [filters.start_date, filters.end_date, pagination.current_page]);

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleApplyFilters = () => {
    setPagination((prev) => ({ ...prev, current_page: 1 }));
  };

  const handleClearFilters = () => {
    setFilters({
      start_date: "",
      end_date: "",
    });
    setPagination((prev) => ({ ...prev, current_page: 1 }));
  };

  const handlePageChange = (event, value) => {
    setPagination((prev) => ({ ...prev, current_page: value }));
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      minimumFractionDigits: 2,
    }).format(amount || 0);
  };

  const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  };

  if (loading && reports.length === 0) {
    return (
      <Box
        display="flex"
        justifyContent="center"
        alignItems="center"
        minHeight="400px"
      >
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Container maxWidth="xl" sx={{ mt: 4, mb: 4 }}>
     

      <Card sx={{ mb: 3, boxShadow: 1 }}>
        <CardContent>
          <Grid container spacing={2} alignItems="flex-end">
            <Grid size={{ xs: 12, sm: 6, md: 5 }}>
              <TextField
                fullWidth
                label="Start Date"
                type="date"
                name="start_date"
                value={filters.start_date}
                onChange={handleFilterChange}
                slotProps={{
                  inputLabel: { shrink: true },
                }}
                variant="outlined"
              />
            </Grid>

            <Grid size={{ xs: 12, sm: 6, md: 5 }}>
              <TextField
                fullWidth
                label="End Date"
                type="date"
                name="end_date"
                value={filters.end_date}
                onChange={handleFilterChange}
                slotProps={{
                  inputLabel: { shrink: true },
                }}
                variant="outlined"
              />
            </Grid>

            <Grid size={{ xs: 12, sm: 6, md: 1 }}>
              <Button
                fullWidth
                sx={{ p: 2, py: 2 }}
                variant="contained"
                color="primary"
                onClick={handleApplyFilters}
              >
                Filter
              </Button>
            </Grid>

            <Grid size={{ xs: 12, sm: 6, md: 1 }}>
              <Button
                fullWidth
                sx={{ p: 2, py: 2 }}
                variant="outlined"
                onClick={handleClearFilters}
              >
                Clear
              </Button>
            </Grid>
          </Grid>
        </CardContent>
      </Card>

      <TableContainer component={Paper}>
        <Table sx={{ minWidth: 1200 }}>
          <TableHead>
            <TableRow sx={{ backgroundColor: "primary.light" }}>
              <TableCell sx={{ fontWeight: "bold" }}>Report Date</TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Total Shipments
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Total Revenue
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Delivered
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Pending Assigned
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Pending Payment
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Picked Up
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                In Transit
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Out for Delivery
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Failed Delivery
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Delayed
              </TableCell>
              <TableCell sx={{ fontWeight: "bold" }} align="right">
                Canceled
              </TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {reports.length === 0 ? (
              <TableRow>
                <TableCell colSpan={12} align="center" sx={{ py: 4 }}>
                  <Alert severity="info" sx={{ justifyContent: "center" }}>
                    No reports found for the selected date range
                  </Alert>
                </TableCell>
              </TableRow>
            ) : (
              reports.map((report) => (
                <TableRow key={report.id} hover>
                  <TableCell>{formatDate(report.report_date)}</TableCell>
                  <TableCell align="right">
                    {report.total_shipments?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell
                    align="right"
                    sx={{ color: "success.main", fontWeight: "bold" }}
                  >
                    {formatCurrency(report.total_revenue)}
                  </TableCell>
                  <TableCell align="right">
                    {report.delivered?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.pending_assigned?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.pending_payment?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.picked_up?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.in_transit?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.out_for_delivery?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.failed_delivery?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.delayed?.toLocaleString() || 0}
                  </TableCell>
                  <TableCell align="right">
                    {report.canceled?.toLocaleString() || 0}
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {pagination.last_page > 1 && (
        <Box display="flex" justifyContent="center" sx={{ mt: 3 }}>
          <Pagination
            count={pagination.last_page}
            page={pagination.current_page}
            onChange={handlePageChange}
            color="primary"
            size="large"
            showFirstButton
            showLastButton
          />
        </Box>
      )}
    </Container>
  );
};

export default DailyReport;
