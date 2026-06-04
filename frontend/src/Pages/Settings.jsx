// Settings.jsx
import React, { useState, useEffect } from "react";
import {
  Container,
  Typography,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Box,
  CircularProgress,
} from "@mui/material";
import {
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
} from "@mui/icons-material";
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import settingsService from "../services/settingsService";
import LoadingSpinner from "../Components/LoadingSpinner";

const Settings = () => {
  const [settings, setSettings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [openAddModal, setOpenAddModal] = useState(false);
  const [openEditModal, setOpenEditModal] = useState(false);
  const [openDeleteModal, setOpenDeleteModal] = useState(false);
  const [selectedSetting, setSelectedSetting] = useState(null);
  const [formData, setFormData] = useState({ key: "", value: "" });
  const [errors, setErrors] = useState({});
  const fetchSettings = async () => {
    try {
      setLoading(true);
      const response = await settingsService.getServices();
      if (response.success) {
        setSettings(response.data);
      }
    } catch (error) {
      toast.error("Failed to fetch settings");
      console.error("Error fetching settings:", error);
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => {
    fetchSettings();
  }, []);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: "" }));
    }
  };

  const handleAddSubmit = async () => {
    try {
      const response = await settingsService.createServices(formData);
      if (response.success) {
        toast.success(response.message || "Setting added successfully");

        setOpenAddModal(false);
        setFormData({ key: "", value: "" });

        fetchSettings();
      }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        setErrors(error.response.data.errors);
      } else {
        toast.error("Failed to add setting");
      }
    }
  };

  const handleEditClick = (setting) => {
    setSelectedSetting(setting);
    setFormData({ key: setting.key, value: setting.value });
    setOpenEditModal(true);
  };

  const handleEditSubmit = async () => {
    try {
      const response = await settingsService.updateServices(
        selectedSetting.id,
        {
          value: formData.value,
        },
      );
      if (response.success) {
        toast.success(response.message || "Setting updated successfully");
        setOpenEditModal(false);
        setSelectedSetting(null);
        setFormData({ key: "", value: "" });
        fetchSettings();
      }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        setErrors(error.response.data.errors);
      } else {
        toast.error("Failed to update setting");
      }
    }
  };

  const handleDeleteClick = (setting) => {
    setSelectedSetting(setting);
    setOpenDeleteModal(true);
  };

  const handleDeleteConfirm = async () => {
    try {
      const response = await settingsService.deleteService(selectedSetting.id);
      if (response.success) {
        toast.success(response.message || "Setting deleted successfully");
        setOpenDeleteModal(false);
        setSelectedSetting(null);
        fetchSettings();
      }
    } catch (error) {
      toast.error(error);
    }
  };

  const handleCloseModals = () => {
    setOpenAddModal(false);
    setOpenEditModal(false);
    setOpenDeleteModal(false);
    setSelectedSetting(null);
    setFormData({ key: "", value: "" });
    setErrors({});
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          mb: 3,
        }}
      >
        <Typography variant="h4" component="h1">
          Settings
        </Typography>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={() => setOpenAddModal(true)}
        >
          Add Setting
        </Button>
      </Box>

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>
                <strong>Key</strong>
              </TableCell>
              <TableCell>
                <strong>Value</strong>
              </TableCell>
              <TableCell>
                <strong>Actions</strong>
              </TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {settings.length === 0 ? (
              <TableRow>
                <TableCell colSpan={3} align="center">
                  No settings found
                </TableCell>
              </TableRow>
            ) : (
              settings.map((setting) => (
                <TableRow key={setting.id}>
                  <TableCell>{setting.key}</TableCell>
                  <TableCell>{setting.value}</TableCell>
                  <TableCell>
                    <IconButton
                      color="primary"
                      onClick={() => handleEditClick(setting)}
                      size="small"
                    >
                      <EditIcon />
                    </IconButton>
                    <IconButton
                      color="error"
                      onClick={() => handleDeleteClick(setting)}
                      size="small"
                    >
                      <DeleteIcon />
                    </IconButton>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <Dialog
        open={openAddModal}
        onClose={handleCloseModals}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Add Setting</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            name="key"
            label="Key"
            type="text"
            fullWidth
            variant="outlined"
            value={formData.key}
            onChange={handleInputChange}
            error={!!errors.key}
            helperText={errors.key?.[0]}
            sx={{ mb: 2 }}
          />
          <TextField
            margin="dense"
            name="value"
            label="Value"
            type="text"
            fullWidth
            variant="outlined"
            value={formData.value}
            onChange={handleInputChange}
            error={!!errors.value}
            helperText={errors.value?.[0]}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseModals}>Cancel</Button>
          <Button onClick={handleAddSubmit} variant="contained" color="primary">
            Save
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={openEditModal}
        onClose={handleCloseModals}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Edit Setting</DialogTitle>
        <DialogContent>
          <TextField
            margin="dense"
            name="key"
            label="Key"
            type="text"
            fullWidth
            variant="outlined"
            value={formData.key}
            disabled
            sx={{ mb: 2 }}
          />
          <TextField
            margin="dense"
            name="value"
            label="Value"
            type="text"
            fullWidth
            variant="outlined"
            value={formData.value}
            onChange={handleInputChange}
            error={!!errors.value}
            helperText={errors.value?.[0]}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseModals}>Cancel</Button>
          <Button
            onClick={handleEditSubmit}
            variant="contained"
            color="primary"
          >
            Update
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={openDeleteModal}
        onClose={handleCloseModals}
        maxWidth="xs"
        fullWidth
      >
        <DialogTitle>Confirm Delete</DialogTitle>
        <DialogContent>
          <Typography>
            Are you sure you want to delete key "{selectedSetting?.key}"?
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseModals}>Cancel</Button>
          <Button
            onClick={handleDeleteConfirm}
            variant="contained"
            color="error"
          >
            Delete
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
};

export default Settings;
