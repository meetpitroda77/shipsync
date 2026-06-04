import { useContext, useEffect, useState } from "react";
import {
  Box,
  Button,
  Pagination,
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
  Select,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
} from "@mui/material";

import LoadingSpinner from "../../Components/LoadingSpinner";
import { useNavigate } from "react-router-dom";
import { AuthContext } from "../../context/UserContext";
import { toast } from "react-toastify";
import userService from "../../services/userService"; // ✅ Fixed import

const Users = () => {
  const [Users, setUsers] = useState([]);
  const { user } = useContext(AuthContext);

  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const [search, setSearch] = useState("");
  const [appliedSearch, setAppliedSearch] = useState("");

  const [page, setPage] = useState(1);

  const [sortField, setSortField] = useState("created_at");
  const [sortDirection, setSortDirection] = useState("desc");
  const [role, setRole] = useState("");
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");

  const [deleteDialog, setDeleteDialog] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState(null);

  const fetchUsers = async ({
    currentPage = page,
    currentSearch = appliedSearch,
  } = {}) => {
    try {
      setLoading(true);

      const response = await userService.getUser(
        currentPage,
        currentSearch,
        sortField,
        sortDirection,
        role,
        startDate,
        endDate,
      );
      setUsers(response.data);
      setMeta(response.meta);
    } catch (error) {
      console.error(error);
      toast.error("Failed to fetch users");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, [page, sortField, sortDirection, appliedSearch, role, startDate, endDate]);

  const handleFilter = () => {
    setAppliedSearch(search);
    setPage(1);
  };

  const handleClearFilters = () => {
    setSearch("");
    setAppliedSearch("");
    setRole("");
    setStartDate("");
    setEndDate("");
    setPage(1);
    setSortField("created_at");
    setSortDirection("desc");
  };
  const handleSort = (field) => {
    if (sortField === field) {
      setSortDirection(sortDirection === "asc" ? "desc" : "asc");
    } else {
      setSortField(field);
      setSortDirection("asc");
    }
  };

  const sortArrow = (field) => {
    if (sortField !== field) return "";
    return sortDirection === "asc" ? " ↑" : " ↓";
  };

  const handleDelete = async (id) => {
    setLoading(true);
    try {
      const res = await userService.deleteUser(id);

      toast.success(res.message);

      const updatedUsers = Users.filter((user) => user.id !== id);
      setUsers(updatedUsers);

      setMeta((prevMeta) => ({
        ...prevMeta,
        total: prevMeta.total - 1,
      }));

      if (updatedUsers.length === 0 && page > 1) {
        setPage(page - 1);
      }
    } catch (error) {
      console.error(error);
      toast.error("Failed to delete user");
    } finally {
      setLoading(false);
    }
  };

  if (loading && Users.length === 0) {
    return (
      <Box p={4}>
        <Box
          display="flex"
          justifyContent="center"
          alignItems="center"
          minHeight="400px"
        >
          <LoadingSpinner />
        </Box>
      </Box>
    );
  }
  const handleRoleChange = async (userId, role) => {
    try {
      const response = await userService.updateRole(userId, {
        user_id: userId,
        role,
      });

      toast.success(response.message);

      setUsers((prev) =>
        prev.map((user) => (user.id === userId ? { ...user, role } : user)),
      );
    } catch (error) {
      console.error(error);
      toast.error("Failed to update role");
    }
  };
  const openDeleteDialog = (id) => {
    setSelectedUserId(id);
    setDeleteDialog(true);
  };

  const confirmDelete = async () => {
    await handleDelete(selectedUserId);
    setDeleteDialog(false);
    setSelectedUserId(null);
  };

  return (
    <Box p={4}>
      <Box
        sx={{
          mb: 4,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          flexWrap: "wrap",
          gap: 2,
        }}
      >
        <Typography variant="h5" fontWeight="bold">
          Users
        </Typography>

        <Button
          variant="contained"
          color="primary"
          onClick={() => navigate("/admin/users/create")}
        >
          Create User
        </Button>
      </Box>

      <Box mb={4}></Box>
      <Box mb={4}>
        <Card elevation={2} sx={{ borderRadius: 3 }}>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, sm: 6, md: 10 }}>
                <TextField
                  fullWidth
                  label="Search User"
                  placeholder="ID / Name / Email"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  size="small"
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <Stack
                  direction={{ xs: "column", sm: "row" }}
                  spacing={2}
                  sx={{ justifyContent: "flex-end" }}
                >
                  <Button
                    variant="contained"
                    size="large"
                    onClick={handleFilter}
                  >
                    Search
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
              <Grid size={{ xs: 12, sm: 6, md: 4 }}>
                <Select
                  fullWidth
                  size="small"
                  value={role}
                  displayEmpty
                  onChange={(e) => setRole(e.target.value)}
                >
                  <MenuItem value="">All Roles</MenuItem>
                  <MenuItem value="admin">Admin</MenuItem>
                  <MenuItem value="staff">Staff</MenuItem>
                  <MenuItem value="agent">Agent</MenuItem>
                  <MenuItem value="customer">Customer</MenuItem>
                </Select>
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 4 }}>
                <TextField
                  fullWidth
                  size="small"
                  type="date"
                  label="Start Date"
                  slotProps={{
                    inputLabel: { shrink: true },
                  }}
                  value={startDate}
                  onChange={(e) => setStartDate(e.target.value)}
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 4 }}>
                <TextField
                  fullWidth
                  size="small"
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
                align="center"
                onClick={() => handleSort("id")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                ID{sortArrow("id")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("name")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Name{sortArrow("name")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("email")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Email{sortArrow("email")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("role")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Role{sortArrow("role")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("email_verified_at")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Email Verified{sortArrow("email_verified_at")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("created_at")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Created At{sortArrow("created_at")}
              </TableCell>

              <TableCell align="center">Actions</TableCell>
            </TableRow>
          </TableHead>
          <Dialog open={deleteDialog} onClose={() => setDeleteDialog(false)}>
            <DialogTitle>Delete User</DialogTitle>

            <DialogContent>
              Are you sure you want to delete this user?
            </DialogContent>

            <DialogActions>
              <Button onClick={() => setDeleteDialog(false)}>Cancel</Button>

              <Button color="error" variant="contained" onClick={confirmDelete}>
                Delete
              </Button>
            </DialogActions>
          </Dialog>
          <TableBody>
            {Users.length > 0 ? (
              Users.map((User) => (
                <TableRow key={User.id}>
                  <TableCell align="center">{User.id}</TableCell>
                  <TableCell align="center">{User.name}</TableCell>
                  <TableCell align="center">{User.email}</TableCell>
                  <TableCell align="center">
                    <Select
                      size="small"
                      value={User.role}
                      onChange={(e) =>
                        handleRoleChange(User.id, e.target.value)
                      }
                      sx={{ minWidth: 120 }}
                    >
                      <MenuItem value="admin">Admin</MenuItem>
                      <MenuItem value="staff">Staff</MenuItem>
                      <MenuItem value="agent">Agent</MenuItem>
                      <MenuItem value="customer">Customer</MenuItem>
                    </Select>
                  </TableCell>
                  <TableCell align="center">
                    {User.email_verified_at
                      ? new Date(User.email_verified_at).toLocaleDateString()
                      : "Not Verified"}
                  </TableCell>
                  <TableCell align="center">
                    {new Date(User.created_at).toLocaleDateString()}
                  </TableCell>
                  <TableCell align="center">
                    <Button
                      variant="outlined"
                      color="error"
                      size="small"
                      onClick={() => openDeleteDialog(User.id)}
                    >
                      Delete
                    </Button>
                  </TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={7} align="center">
                  {loading ? <LoadingSpinner /> : "No users found"}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {Users.length > 0 && (
        <Box mt={3} sx={{ display: "flex", justifyContent: "center", mt: 2 }}>
          <Pagination
            count={meta.last_page || 1}
            page={page}
            onChange={(e, value) => setPage(value)}
            color="primary"
          />
        </Box>
      )}
    </Box>
  );
};

export default Users;
