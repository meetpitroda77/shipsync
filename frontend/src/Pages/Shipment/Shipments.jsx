import { useContext, useEffect, useState } from "react";
import {
  Box,
  Button,
  Chip,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
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
  Typography,
  Stack,
  Grid,
  CardContent,
  Card,
  FormHelperText,
} from "@mui/material";

import shipmentService from "../../services/shipmentService";
import LoadingSpinner from "../../Components/LoadingSpinner";
import { toast } from "react-toastify";
import { useNavigate } from "react-router-dom";
import { AuthContext } from "../../context/UserContext";

const statusOptions = [
  "pending_assigned",
  "pending_payment",
  "assigned",
  "picked_up",
  "in_transit",
  "out_for_delivery",
  "delivered",
  "failed_delivery",
  "delayed",
  "canceled",
];

const Shipments = () => {
  const [shipments, setShipments] = useState([]);
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);

  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const [userRole, setUserRole] = useState("");

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("");
  const [deliveryMethod, setDeliveryMethod] = useState("");
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");

  const [page, setPage] = useState(1);

  const [sortField, setSortField] = useState("created_at");
  const [sortDirection, setSortDirection] = useState("desc");

  const [openManage, setOpenManage] = useState(false);
  const [selectedShipment, setSelectedShipment] = useState(null);

  const [updateStatus, setUpdateStatus] = useState("");
  const [agentId, setAgentId] = useState("");
  const [agents, setAgents] = useState([]);
  const [deliveryProof, setDeliveryProof] = useState(null);
  const [failedReason, setFailedReason] = useState("");

  const [errors, setErrors] = useState({});
  const [updateLoading, setUpdateLoading] = useState(false);

  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [deleteShipmentId, setDeleteShipmentId] = useState(null);
  const [deleteLoading, setDeleteLoading] = useState(false);

  const fetchShipments = async ({
    currentPage = page,
    currentSearch = search,
    currentStatus = status,
    currentDeliveryMethod = deliveryMethod,
    currentStartDate = startDate,
    currentEndDate = endDate,
  } = {}) => {
    try {
      setLoading(true);

      const response = await shipmentService.getShipments(
        currentPage,
        currentSearch,
        sortField,
        sortDirection,
        currentStatus,
        currentDeliveryMethod,
        currentStartDate,
        currentEndDate,
      );

      setShipments(response.data);
      setAgents(response.users || []);
      setMeta(response.meta);

      if (response.user_role) {
        setUserRole(response.user_role);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchShipments();
  }, [page, sortField, sortDirection]);
  const handleFilter = () => {
    setPage(1);

    fetchShipments({
      currentPage: 1,
    });
  };
  const handleClearFilters = () => {
    setSearch("");
    setStatus("");
    setDeliveryMethod("");
    setStartDate("");
    setEndDate("");
    setPage(1);

    fetchShipments({
      currentPage: 1,
      currentSearch: "",
      currentStatus: "",
      currentDeliveryMethod: "",
      currentStartDate: "",
      currentEndDate: "",
    });
  };

  const handleSort = (field) => {
    if (sortField === field) {
      setSortDirection(sortDirection === "asc" ? "desc" : "asc");
    } else {
      setSortField(field);
      setSortDirection("asc");
    }
  };

  const handleStatusUpdate = async () => {
    try {
      setUpdateLoading(true);
      setErrors({});

      const formData = new FormData();

      formData.append("status", updateStatus);

      if (agentId && (userRole === "staff" || userRole === "admin")) {
        formData.append("agentid", agentId);
      }
      if (deliveryProof) formData.append("delivery_proof", deliveryProof);
      if (failedReason) formData.append("failed_reason", failedReason);

      const res = await shipmentService.updateShipmentStatus(
        selectedShipment.id,
        formData,
      );

      toast.success(res.message);
      setOpenManage(false);
      setUpdateStatus("");
      setAgentId("");
      setDeliveryProof(null);
      setFailedReason("");
      setErrors({});
      fetchShipments();
    } catch (error) {
      if (error.response?.status === 422 && error.response?.data?.errors) {
        const errorObj = {};
        error.response.data.errors.forEach((err) => {
          errorObj[err.field] = err.message;
        });
        setErrors(errorObj);
      } else {
        console.error(error);
      }
    } finally {
      setUpdateLoading(false);
    }
  };

  const handleDelete = async () => {
    try {
      setDeleteLoading(true);

      const res = await shipmentService.deleteShipment(deleteShipmentId);

      toast.success(res.message);

      setOpenDeleteDialog(false);
      setDeleteShipmentId(null);

      fetchShipments();
      navigate(`/${user?.role}/shipments`);
    } catch (error) {
      console.error(error);
      toast.error("Failed to delete shipment");
    } finally {
      setDeleteLoading(false);
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case "delivered":
        return "success";
      case "in_transit":
        return "info";
      case "failed_delivery":
        return "error";
      case "pending_payment":
        return "warning";
      default:
        return "default";
    }
  };

  const sortArrow = (field) => {
    if (sortField !== field) return "";
    return sortDirection === "asc" ? " ↑" : " ↓";
  };

  const canAssignAgent = () => {
    return ["staff", "admin"].includes(userRole);
  };

  const handleOpenManage = (shipment) => {
    setSelectedShipment(shipment);
    setUpdateStatus("");
    setAgentId(shipment.assigned_to || "");
    setDeliveryProof(null);
    setFailedReason("");
    setErrors({});
    setOpenManage(true);
  };

  return (
    <Box p={4}>
      <Box mb={4}>
        <Card elevation={2} sx={{ borderRadius: 3 }}>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, sm: 12, md: 10 }}>
                <TextField
                  fullWidth
                  label="Search Shipment"
                  placeholder="Tracking ID / Sender / Receiver"
                  value={search}
                  size="small"
                  onChange={(e) => setSearch(e.target.value)}
                />
              </Grid>
              <Grid size={{ xs: 12, sm: 12, md: 2 }}>
                <Box sx={{ display: "flex", gap: 1 }}>
                  <Button
                    variant="contained"
                    fullWidth
                    onClick={handleFilter}
                    sx={{
                      height: { xs: 42, sm: 42, md: "auto" },
                      fontSize: { xs: "0.875rem", sm: "1rem" },
                    }}
                  >
                    Filter
                  </Button>

                  <Button
                    variant="outlined"
                    fullWidth
                    onClick={handleClearFilters}
                    sx={{
                      height: { xs: 42, sm: 42, md: "auto" },
                      fontSize: { xs: "0.875rem", sm: "1rem" },
                    }}
                  >
                    Clear
                  </Button>
                </Box>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <FormControl fullWidth size="small">
                  <InputLabel>Status</InputLabel>
                  <Select
                    value={status}
                    label="Status"
                    onChange={(e) => setStatus(e.target.value)}
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

              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <FormControl fullWidth size="small">
                  <InputLabel>Delivery</InputLabel>
                  <Select
                    value={deliveryMethod}
                    label="Delivery"
                    onChange={(e) => setDeliveryMethod(e.target.value)}
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="standard">Standard</MenuItem>
                    <MenuItem value="express">Express</MenuItem>
                  </Select>
                </FormControl>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <TextField
                  size="small"
                  fullWidth
                  type="date"
                  label="Start Date"
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                  value={startDate}
                  onChange={(e) => setStartDate(e.target.value)}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                <TextField
                  size="small"
                  fullWidth
                  type="date"
                  label="End Date"
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                  value={endDate}
                  onChange={(e) => setEndDate(e.target.value)}
                />
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
                onClick={() => handleSort("sender_name")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Sender{sortArrow("sender_name")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("receiver_name")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Receiver{sortArrow("receiver_name")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("status")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Status{sortArrow("status")}
              </TableCell>

              <TableCell
                onClick={() => handleSort("created_at")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Created At{sortArrow("created_at")}
              </TableCell>

              <TableCell>Actions</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {shipments.length > 0 ? (
              shipments.map((shipment) => (
                <TableRow key={shipment.id}>
                  <TableCell>{shipment.tracking_id}</TableCell>
                  <TableCell>{shipment.sender_name}</TableCell>
                  <TableCell>{shipment.receiver_name}</TableCell>

                  <TableCell>
                    <Chip
                      label={shipment.status}
                      color={getStatusColor(shipment.status)}
                    />
                  </TableCell>

                  <TableCell>
                    {new Date(shipment.created_at).toLocaleDateString()}
                  </TableCell>

                  <TableCell>
                    <Stack direction="row" spacing={1}>
                      {userRole !== "customer" && (
                        <Button
                          variant="outlined"
                          onClick={() => handleOpenManage(shipment)}
                        >
                          Manage
                        </Button>
                      )}

                      <Button
                        variant="outlined"
                        onClick={() =>
                          navigate(`/${user?.role}/shipments/${shipment.id}`)
                        }
                      >
                        View
                      </Button>
                      {userRole !== "customer" ||
                        (userRole !== "agent" && (
                          <Button
                            variant="outlined"
                            color="error"
                            onClick={() => {
                              setDeleteShipmentId(shipment.id);
                              setOpenDeleteDialog(true);
                            }}
                          >
                            Delete
                          </Button>
                        ))}
                    </Stack>
                  </TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={6} align="center">
                  {loading ? <LoadingSpinner /> : "No shipments found"}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {shipments.length > 0 && (
        <Box mt={3} sx={{ mt: 3, display: "flex", justifyContent: "center" }}>
          <Pagination
            count={meta.last_page || 1}
            page={page}
            onChange={(e, value) => setPage(value)}
            color="primary"
          />
        </Box>
      )}

      <Dialog
        open={openDeleteDialog}
        onClose={() => {
          if (!deleteLoading) {
            setOpenDeleteDialog(false);
            setDeleteShipmentId(null);
          }
        }}
      >
        <DialogTitle>Delete Shipment</DialogTitle>

        <DialogContent>
          <Typography>
            Are you sure you want to delete this shipment?
          </Typography>
        </DialogContent>

        <DialogActions>
          <Button
            onClick={() => {
              setOpenDeleteDialog(false);
              setDeleteShipmentId(null);
            }}
            disabled={deleteLoading}
          >
            Cancel
          </Button>

          <Button
            color="error"
            variant="contained"
            onClick={handleDelete}
            disabled={deleteLoading}
          >
            {deleteLoading ? "Deleting..." : "Delete"}
          </Button>
        </DialogActions>
      </Dialog>
      <Dialog open={openManage} onClose={() => setOpenManage(false)} fullWidth>
        <DialogTitle>
          Manage Shipment - {selectedShipment?.tracking_id}
        </DialogTitle>

        <DialogContent>
          {canAssignAgent() && (
            <FormControl fullWidth sx={{ mt: 2 }} error={!!errors.agentid}>
              <InputLabel>Assign to Delivery Agent</InputLabel>
              <Select
                value={agentId}
                label="Assign to Delivery Agent"
                onChange={(e) => {
                  setAgentId(e.target.value);
                  if (errors.agentid) {
                    setErrors((prev) => ({ ...prev, agentid: "" }));
                  }
                }}
              >
                <MenuItem value="">
                  <em>Select Agent</em>
                </MenuItem>
                {agents.map((agent) => (
                  <MenuItem key={agent.id} value={agent.id}>
                    {agent.name}
                  </MenuItem>
                ))}
              </Select>
              {errors.agentid && (
                <FormHelperText>{errors.agentid}</FormHelperText>
              )}
            </FormControl>
          )}

          <FormControl
            fullWidth
            sx={{ mt: canAssignAgent() ? 2 : 2 }}
            error={!!errors.status}
          >
            <InputLabel>Change Status</InputLabel>
            <Select
              value={updateStatus}
              label="Change Status"
              onChange={(e) => {
                setUpdateStatus(e.target.value);
                if (errors.status) {
                  setErrors((prev) => ({ ...prev, status: "" }));
                }
              }}
            >
              <MenuItem value="">
                <em>Open this select Status</em>
              </MenuItem>
              {statusOptions.map((item) => {
                if (
                  userRole === "agent" &&
                  (item === "assigned" ||
                    item === "canceled" ||
                    item === "pending_assigned" ||
                    item === "pending_payment")
                ) {
                  return null;
                }
                return (
                  <MenuItem key={item} value={item}>
                    {item.replaceAll("_", " ")}
                  </MenuItem>
                );
              })}
            </Select>
            {errors.status && <FormHelperText>{errors.status}</FormHelperText>}
          </FormControl>

          {updateStatus === "delivered" && (
            <Box sx={{ mt: 2 }}>
              <Typography variant="body2" sx={{ mb: 1, fontWeight: 500 }}>
                Upload Delivery Proof (Image)
              </Typography>
              <TextField
                type="file"
                fullWidth
                size="small"
                InputProps={{
                  inputProps: { accept: "image/*" },
                }}
                onChange={(e) => {
                  setDeliveryProof(e.target.files[0]);
                  if (errors.delivery_proof) {
                    setErrors((prev) => ({ ...prev, delivery_proof: "" }));
                  }
                }}
                error={!!errors.delivery_proof}
                helperText={errors.delivery_proof}
              />
              {deliveryProof && (
                <Typography
                  variant="caption"
                  color="textSecondary"
                  sx={{ mt: 0.5, display: "block" }}
                >
                  Selected: {deliveryProof.name}
                </Typography>
              )}
            </Box>
          )}
          {updateStatus === "failed_delivery" && (
            <FormControl
              fullWidth
              sx={{ mt: 2 }}
              error={!!errors.failed_reason}
            >
              <TextField
                fullWidth
                multiline
                rows={3}
                label="Failure Reason"
                value={failedReason}
                onChange={(e) => {
                  setFailedReason(e.target.value);
                  if (errors.failed_reason) {
                    setErrors((prev) => ({ ...prev, failed_reason: "" }));
                  }
                }}
                placeholder="Enter reason"
                error={!!errors.failed_reason}
              />
              {errors.failed_reason && (
                <FormHelperText>{errors.failed_reason}</FormHelperText>
              )}
            </FormControl>
          )}
        </DialogContent>

        <DialogActions>
          <Button
            onClick={() => {
              setOpenManage(false);
              setErrors({});
            }}
          >
            Cancel
          </Button>
          <Button
            variant="contained"
            onClick={handleStatusUpdate}
            disabled={updateLoading}
          >
            {updateLoading ? "Updating..." : "Update Status"}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default Shipments;
