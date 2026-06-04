import { useState } from "react";
import {
  Box,
  Button,
  Card,
  CardContent,
  CircularProgress,
  Container,
  FormControl,
  Grid,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Typography,
} from "@mui/material";
import { Save } from "@mui/icons-material";
import { toast } from "react-toastify";

import userService from "../../services/userService";

export const CreateUser = () => {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    password: "",
    role: "",
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;

    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));

    setErrors((prev) => ({
      ...prev,
      [name]: "",
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    setErrors({});

    try {
      setLoading(true);

      const response = await userService.createUser(formData);

      if (response.success) {
        toast.success(response.message);

        setFormData({
          name: "",
          email: "",
          password: "",
          role: "",
        });
      }
    } catch (error) {
      console.error(error);

      if (error.response?.status === 422) {
        setErrors(error?.response?.data?.errors);
      } else {
        toast.error(error.response?.data?.message || "Failed to create user");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container maxWidth="sm" sx={{ mt: 4, mb: 4 }}>
      <Card elevation={3}>
        <CardContent>
          <Typography
            variant="h5"
            fontWeight="bold"
            mb={3}
            sx={{ fontWeight: "bold", textAlign: "center", mb: 3 }}
          >
            Create User
          </Typography>

          <Box component="form" onSubmit={handleSubmit}>
            <Grid container spacing={3}>
              <Grid size={{ xs: 12 }}>
                <TextField
                  fullWidth
                  label="Name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  error={!!errors.name}
                  helperText={errors.name}
                />
              </Grid>

              <Grid size={{ xs: 12 }}>
                <TextField
                  fullWidth
                  label="Email"
                  name="email"
                  type="email"
                  value={formData.email}
                  onChange={handleChange}
                  error={!!errors.email}
                  helperText={errors.email}
                />
              </Grid>

              {/* Password */}
              <Grid size={{ xs: 12 }}>
                <TextField
                  fullWidth
                  label="Password"
                  name="password"
                  type="password"
                  value={formData.password}
                  onChange={handleChange}
                  error={!!errors.password}
                  helperText={errors.password}
                />
              </Grid>

              {/* Role */}
              <Grid size={{ xs: 12 }}>
                <FormControl fullWidth error={!!errors.role}>
                  <InputLabel>Role</InputLabel>

                  <Select
                    name="role"
                    value={formData.role}
                    label="Role"
                    onChange={handleChange}
                  >
                    <MenuItem value="admin">Admin</MenuItem>
                    <MenuItem value="staff">Staff</MenuItem>
                    <MenuItem value="agent">Agent</MenuItem>
                    <MenuItem value="customer">Customer</MenuItem>
                  </Select>

                  {errors.role && (
                    <Typography
                      variant="caption"
                      color="error"
                      sx={{ ml: 2, mt: 0.5 }}
                    >
                      {errors.role}
                    </Typography>
                  )}
                </FormControl>
              </Grid>

              {/* Submit Button */}
              <Grid size={{ xs: 12 }}>
                <Button
                  fullWidth
                  type="submit"
                  variant="contained"
                  size="large"
                  startIcon={
                    loading ? (
                      <CircularProgress size={20} color="inherit" />
                    ) : (
                      <Save />
                    )
                  }
                  disabled={loading}
                >
                  {loading ? "Creating..." : "Create User"}
                </Button>
              </Grid>
            </Grid>
          </Box>
        </CardContent>
      </Card>
    </Container>
  );
};
