import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import {
  Box,
  Button,
  FormControl,
  InputLabel,
  MenuItem,
  Pagination,
  Select,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  TextField,
  Stack,
  Grid,
  CardContent,
  Card,
} from "@mui/material";

import shipmentService from "../services/shipmentService";
import LoadingSpinner from "../Components/LoadingSpinner";

const statusOptions = ["pending", "paid", "failed"];

const Payments = () => {
  const [searchParams, setSearchParams] = useSearchParams();

  const [Payments, setPayments] = useState([]);
  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const search = searchParams.get("search") || "";
  const status = searchParams.get("status") || "";
  const startDate = searchParams.get("startDate") || "";
  const endDate = searchParams.get("endDate") || "";
  const page = Number(searchParams.get("page")) || 1;
  const sortField = searchParams.get("sortField") || "created_at";
  const sortDirection = searchParams.get("sortDirection") || "desc";

  const [filterSearch, setFilterSearch] = useState(search);
  const [filterStatus, setFilterStatus] = useState(status);
  const [filterStartDate, setFilterStartDate] = useState(startDate);
  const [filterEndDate, setFilterEndDate] = useState(endDate);
  // const [search, setSearch] = useState(searchParams.get("search") || "");
  // const [status, setStatus] = useState(searchParams.get("status") || "");
  // const [startDate, setStartDate] = useState(
  //   searchParams.get("startDate") || "",
  // );
  // const [endDate, setEndDate] = useState(searchParams.get("endDate") || "");
  // const [page, setPage] = useState(Number(searchParams.get("page")) || 1);
  // const [sortField, setSortField] = useState(
  //   searchParams.get("sortField") || "created_at",
  // );
  // const [sortDirection, setSortDirection] = useState(
  //   searchParams.get("sortDirection") || "desc",
  // );

  const updateURLParams = (updates) => {
    const newParams = new URLSearchParams(searchParams);

    Object.entries(updates).forEach(([key, value]) => {
      if (value && value !== "") {
        newParams.set(key, value);
      } else {
        newParams.delete(key);
      }
    });

    setSearchParams(newParams);
  };

  const fetchPayments = async ({
    currentPage = page,
    currentSearch = search,
    currentStatus = status,
    currentStartDate = startDate,
    currentEndDate = endDate,
  } = {}) => {
    try {
      setLoading(true);

      const response = await shipmentService.getPayments(
        currentPage,
        currentSearch,
        sortField,
        sortDirection,
        currentStatus,
        currentStartDate,
        currentEndDate,
      );

      setPayments(response.data);
      setMeta(response.meta);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPayments({
      currentPage: page,
      currentSearch: search,
      currentStatus: status,
      currentStartDate: startDate,
      currentEndDate: endDate,
    });
  }, [page, search, status, startDate, endDate, sortField, sortDirection]);
  // useEffect(() => {
  //   const urlPage = searchParams.get("page");
  //   const urlSearch = searchParams.get("search");
  //   const urlStatus = searchParams.get("status");
  //   const urlStartDate = searchParams.get("startDate");
  //   const urlEndDate = searchParams.get("endDate");
  //   const urlSortField = searchParams.get("sortField");
  //   const urlSortDirection = searchParams.get("sortDirection");

  //   if (urlPage) setPage(Number(urlPage));
  //   if (urlSearch) setSearch(urlSearch);
  //   if (urlStatus) setStatus(urlStatus);
  //   if (urlStartDate) setStartDate(urlStartDate);
  //   if (urlEndDate) setEndDate(urlEndDate);
  //   if (urlSortField) setSortField(urlSortField);
  //   if (urlSortDirection) setSortDirection(urlSortDirection);
  // }, []);
  const handleFilter = () => {
    updateURLParams({
      page: 1,
      search: filterSearch,
      status: filterStatus,
      startDate: filterStartDate,
      endDate: filterEndDate,
    });
  };

  const handleClearFilters = () => {
    setFilterSearch("");
    setFilterStatus("");
    setFilterStartDate("");
    setFilterEndDate("");

    setSearchParams({});
  };

  const handleSort = (field) => {
    let newDirection = "asc";

    if (sortField === field) {
      newDirection = sortDirection === "asc" ? "desc" : "asc";
    }

    updateURLParams({
      sortField: field,
      sortDirection: newDirection,
      page: 1,
    });
  };

  const sortArrow = (field) => {
    if (sortField !== field) return "";
    return sortDirection === "asc" ? " ↑" : " ↓";
  };

  // const handleSearchChange = (e) => {
  //   const value = e.target.value;
  //   setSearch(value);
  //   updateURLParams({ search: value, page: 1 });
  // };
  const handleSearchChange = (e) => {
    setFilterSearch(e.target.value);
  };
  // const handleStatusChange = (e) => {
  //   const value = e.target.value;
  //   setStatus(value);
  //   updateURLParams({ status: value, page: 1 });
  // };
  const handleStatusChange = (e) => {
    setFilterStatus(e.target.value);
  };

  const handleStartDateChange = (e) => {
    setFilterStartDate(e.target.value);
  };

  // const handleEndDateChange = (e) => {
  //   const value = e.target.value;
  //   setEndDate(value);
  //   updateURLParams({ endDate: value, page: 1 });
  // };
  const handleEndDateChange = (e) => {
    setFilterEndDate(e.target.value);
  };
  const handlePageChange = (e, value) => {
    updateURLParams({ page: value });
  };

  return (
    <Box p={4}>
      <Box mb={4}>
        <Card elevation={2} sx={{ borderRadius: 3 }}>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, sm: 6, md: 4 }}>
                <TextField
                  fullWidth
                  label="Search Payment"
                  placeholder="Tracking ID / Transaction Id"
                  value={filterSearch}
                  onChange={handleSearchChange}
                  size="small"
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <FormControl fullWidth size="small">
                  <InputLabel>Status</InputLabel>
                  <Select
                    value={filterStatus}
                    label="Status"
                    onChange={handleStatusChange}
                  >
                    <MenuItem value="">All Status</MenuItem>
                    {statusOptions.map((item) => (
                      <MenuItem key={item} value={item}>
                        {item.replaceAll("_", " ")}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <TextField
                  size="small"
                  fullWidth
                  type="date"
                  label="Start Date"
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                  value={filterStartDate}
                  onChange={handleStartDateChange}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <TextField
                  size="small"
                  fullWidth
                  type="date"
                  label="End Date"
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                  value={filterEndDate}
                  onChange={handleEndDateChange}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <Stack
                  direction={{ xs: "column", sm: "row" }}
                  spacing={2}
                  justifyContent="flex-end"
                >
                  <Button
                    variant="contained"
                    size="large"
                    onClick={handleFilter}
                  >
                    Filter
                  </Button>

                  <Button
                    variant="outlined"
                    size="large"
                    onClick={handleClearFilters}
                  >
                    Clear
                  </Button>
                </Stack>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      </Box>

      <TableContainer component={Paper} sx={{ mt: 3 }}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell
                onClick={() => handleSort("tracking_id")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Tracking ID{sortArrow("tracking_id")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("payment_method")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Payment Method{sortArrow("payment_method")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("payment_status")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Status{sortArrow("payment_status")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("paid_at")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Paid At{sortArrow("paid_at")}
              </TableCell>

              <TableCell>Amount</TableCell>
              <TableCell>transaction_id</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {Payments.length > 0 ? (
              Payments.map((Payment) => (
                <TableRow key={Payment.id}>
                  <TableCell>{Payment.tracking_id}</TableCell>
                  <TableCell>{Payment.payment_method}</TableCell>
                  <TableCell>{Payment.payment_status}</TableCell>
                  <TableCell>
                    {new Date(Payment.paid_at).toLocaleDateString()}
                  </TableCell>
                  <TableCell>{Payment.amount}</TableCell>
                  <TableCell>{Payment.transaction_id}</TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={6} align="center">
                  {loading ? <LoadingSpinner /> : "No Payments found"}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {Payments.length > 0 && (
        <Box mt={3} sx={{ mt: 3, display: "flex", justifyContent: "center" }}>
          <Pagination
            count={meta.last_page || 1}
            page={page}
            onChange={handlePageChange}
            color="primary"
          />
        </Box>
      )}
    </Box>
  );
};

export default Payments;
