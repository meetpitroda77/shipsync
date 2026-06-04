import { useState } from "react";
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

const ForgotPassword = () => {
  const [email, setEmail] = useState("");
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();

    setErrors({});

    try {
      setLoading(true);

      const res = await authService.forgotPassword(email);

      toast.success(res.message);

      setEmail("");
      setErrors({});
    } catch (error) {
      const data = error?.response?.data;

      // Laravel validation errors
      if (data?.errors) {
        setErrors(data.errors);
      } else {
        setErrors({
          email: [data?.message || "Something went wrong"],
        });
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container maxWidth="sm" sx={{ py: 8 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" gutterBottom>
          Forgot Password
        </Typography>

        <Typography color="text.secondary" sx={{ mb: 3 }}>
          Enter your email address and we'll send a reset link.
        </Typography>

        <Box component="form" onSubmit={handleSubmit}>
          <TextField
            fullWidth
            label="Email"
            type="email"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);

              if (errors.email) {
                setErrors((prev) => ({
                  ...prev,
                  email: null,
                }));
              }
            }}
            margin="normal"
            error={Boolean(errors.email)}
            helperText={errors.email?.[0]}
          />

          <Button
            type="submit"
            fullWidth
            variant="contained"
            sx={{ mt: 2 }}
            disabled={loading}
          >
            {loading ? "Sending..." : "Send Reset Link"}
          </Button>
        </Box>
      </Paper>
    </Container>
  );
};

export default ForgotPassword;