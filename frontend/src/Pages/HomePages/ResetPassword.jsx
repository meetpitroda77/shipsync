import { useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import {
  Container,
  Paper,
  Typography,
  TextField,
  Button,
  Box,
} from "@mui/material";
import { toast } from "react-toastify";
import authService from "../../services/authService";
import LoadingSpinner from "../../Components/LoadingSpinner";

const ResetPassword = () => {
  const navigate = useNavigate();

  const [searchParams] = useSearchParams();
  const [errors, setErrors] = useState({});
  const token = searchParams.get("token");
  const email = searchParams.get("email");

  const [formData, setFormData] = useState({
    password: "",
    password_confirmation: "",
  });

  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;

    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));

    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: null,
      }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    setErrors({});

    try {
      setLoading(true);

      const res = await authService.resetPassword({
        email,
        token,
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      });

      toast.success(res.message);

      navigate("/login");
    } catch (error) {
      const data = error?.response?.data;

      if (data?.errors) {
        setErrors(data.errors);
      } else {
        setErrors({
          password: [data?.message || "Something went wrong"],
        });
      }
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingSpinner message="Resetting password..." />;
  }
  return (
    <Container maxWidth="sm" sx={{ py: 8 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" gutterBottom>
          Reset Password
        </Typography>

        <Box component="form" onSubmit={handleSubmit}>
          <TextField
            fullWidth
            margin="normal"
            label="New Password"
            type="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            error={Boolean(errors.password)}
            helperText={errors.password?.[0]}
          />

          <TextField
            fullWidth
            margin="normal"
            label="Confirm Password"
            type="password"
            name="password_confirmation"
            value={formData.password_confirmation}
            onChange={handleChange}
            error={Boolean(errors.password_confirmation)}
            helperText={errors.password_confirmation?.[0]}
          />

          <Button
            type="submit"
            variant="contained"
            fullWidth
            sx={{ mt: 2 }}
            disabled={loading}
          >
            {loading ? "Resetting..." : "Reset Password"}
          </Button>
        </Box>
      </Paper>
    </Container>
  );
};

export default ResetPassword;
