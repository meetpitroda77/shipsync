import { useContext, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import {
  Container,
  Box,
  Paper,
  Typography,
  TextField,
  Button,
} from "@mui/material";
import authService from "../../services/authService";
import { AuthContext } from "../../context/UserContext";

const Login = () => {
  const navigate = useNavigate();
  const { setToken, setUser } = useContext(AuthContext);
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  });

  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});

    try {
      const res = await authService.login(formData);

      if (res.data.token) {
        setToken(res.data.token);
        setUser(res.data.user);
        toast.success(res.message);
        navigate(`/${res.data.user.role}`, {
          replace: true,
        });
      }
    } catch (error) {
      if (error.response && error.response.data) {
        const responseData = error.response.data;

        if (responseData.errors) {
          setErrors(responseData.errors);
          return;
        }
      }
    }
  };

  return (
    <Container maxWidth="sm" sx={{ mt: 5 }}>
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
        }}
      >
        <Paper
          elevation={18}
          sx={{
            width: "100%",
            maxWidth: 450,
            p: { xs: 3, md: 4 },
            borderRadius: 3,
          }}
        >
          <Typography
            variant="h4"
            fontWeight="bold"
            align="center"
            gutterBottom
            color="text.primary"
          >
            Login
          </Typography>

          <Box component="form" onSubmit={handleSubmit}>
            <TextField
              fullWidth
              label="Email"
              type="text"
              name="email"
              placeholder="Enter your email"
              margin="normal"
              value={formData.email}
              onChange={(e) => {
                setFormData({ ...formData, email: e.target.value });

                setErrors((prev) => ({
                  ...prev,
                  email: null,
                }));
              }}
              error={!!errors.email}
              helperText={errors.email ? errors.email[0] : ""}
            />
            <Box sx={{ textAlign: "right", mt: 1 }}>
              <Link
                to="/forgot-password"
                style={{
                  textDecoration: "none",
                }}
              >
                Forgot Password?
              </Link>
            </Box>

            <TextField
              fullWidth
              label="Password"
              type="password"
              name="password"
              placeholder="Enter your password"
              margin="normal"
              value={formData.password}
              onChange={(e) => {
                setFormData({ ...formData, password: e.target.value });

                setErrors((prev) => ({
                  ...prev,
                  password: null,
                }));
              }}
              error={!!errors.password}
              helperText={errors.password ? errors.password[0] : ""}
            />

            <Box mt={3}>
              <Button
                type="submit"
                fullWidth
                variant="contained"
                size="large"
                sx={{
                  py: 1.3,
                  fontWeight: 600,
                  borderRadius: 2,
                  textTransform: "none",
                }}
              >
                Login
              </Button>
            </Box>

            <Typography
              variant="body2"
              align="center"
              color="text.secondary"
              sx={{ mt: 3 }}
            >
              Don't have an account?{" "}
              <Link
                to="/register"
                style={{
                  color: "#1976d2",
                  fontWeight: 600,
                  textDecoration: "none",
                }}
              >
                Register
              </Link>
            </Typography>
          </Box>
        </Paper>
      </Box>
    </Container>
  );
};

export default Login;
