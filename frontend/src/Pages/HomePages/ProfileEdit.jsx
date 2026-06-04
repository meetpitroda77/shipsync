import { useEffect, useState } from "react";
import {
  Container,
  Card,
  CardHeader,
  CardContent,
  CardActions,
  Grid,
  TextField,
  Button,
  Typography,
  Stack,
  Box,
  IconButton,
  Divider,
  Paper,
  Alert,
} from "@mui/material";
import { toast } from "react-toastify";

import AddIcon from "@mui/icons-material/Add";
import DeleteIcon from "@mui/icons-material/Delete";
import SaveIcon from "@mui/icons-material/Save";
import { useNavigate } from "react-router-dom";
import { useContext } from "react";
import { AuthContext } from "../../context/UserContext";
import LoadingSpinner from "../../Components/LoadingSpinner";
import authService from "../../services/authService";

const ProfileEdit = () => {
  const navigate = useNavigate();
  const { user } = useContext(AuthContext);
  const [loading, setLoading] = useState(false);

  const [formData, setFormData] = useState({
    name: "",
    phone: "",
    addresses: [
      {
        address: "",
        country: "",
        city: "",
        state: "",
        zip_code: "",
      },
    ],
  });

  useEffect(() => {
    const fetchByid = async () => {
      setLoading(true);
      const res = await authService.getUser();
      if (res.success) {
        setFormData(res.user);
        setLoading(false);
      }
    };

    fetchByid();
  }, []);

  const [errors, setErrors] = useState({});
  const [addressErrors, setAddressErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;

    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));

    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: undefined,
      }));
    }
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  const handleAddressChange = (index, field, value) => {
    const updatedAddresses = [...formData.addresses];

    updatedAddresses[index] = {
      ...updatedAddresses[index],
      [field]: value,
    };

    setFormData((prev) => ({
      ...prev,
      addresses: updatedAddresses,
    }));

    const errorKey = `addresses.${index}.${field}`;

    if (errors[errorKey]) {
      setErrors((prev) => ({
        ...prev,
        [errorKey]: undefined,
      }));
    }

    if (addressErrors[index]) {
      const newAddressErrors = { ...addressErrors };
      delete newAddressErrors[index];
      setAddressErrors(newAddressErrors);
    }
  };

  const addAddress = () => {
    setFormData((prev) => ({
      ...prev,
      addresses: [
        ...prev.addresses,
        {
          address: "",
          country: "",
          city: "",
          state: "",
          zip_code: "",
        },
      ],
    }));
  };

  const removeAddress = (index) => {
    if (formData.addresses.length === 1) {
      toast.warning("You must have at least one address");
      return;
    }

    const updatedAddresses = formData.addresses.filter((_, i) => i !== index);

    setFormData((prev) => ({
      ...prev,
      addresses: updatedAddresses,
    }));
  };

  const validateAddresses = () => {
    const newAddressErrors = {};
    let isValid = true;

    formData.addresses.forEach((address, index) => {
      const addressFields = ["address", "country", "city", "state", "zip_code"];
      const missingFields = [];

      addressFields.forEach((field) => {
        if (!address[field] || address[field].trim() === "") {
          missingFields.push(field);
        }
      });

      if (missingFields.length > 0) {
        newAddressErrors[index] = missingFields;
        isValid = false;
      }
    });

    setAddressErrors(newAddressErrors);
    return isValid;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!formData.addresses || formData.addresses.length === 0) {
      toast.error("Please add at least one address");
      return;
    }

    const isAddressesValid = validateAddresses();

    if (!isAddressesValid) {
      toast.error("Please fill in all address fields");
      return;
    }

    try {
      const res = await authService.Updateprofile(formData);

      if (res.success) {
        toast.success(res.message);
        navigate(`/${user?.role}`);
      }
    } catch (error) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors);

        if (error.response.data.errors.addresses) {
          toast.error("Please ensure all addresses are properly filled");
        }
      } else {
        toast.error("Failed to update profile");
      }
    }
  };

  const hasAddressFieldError = (index, field) => {
    return (
      errors[`addresses.${index}.${field}`] ||
      (addressErrors[index] && addressErrors[index].includes(field))
    );
  };

  const getAddressFieldError = (index, field) => {
    if (errors[`addresses.${index}.${field}`]) {
      return errors[`addresses.${index}.${field}`]?.[0];
    }
    if (addressErrors[index] && addressErrors[index].includes(field)) {
      return `${field.replace("_", " ")} is required`;
    }
    return "";
  };

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      <Card elevation={3}>
        <CardHeader
          title="Edit Profile"
          sx={{
            backgroundColor: "primary.main",
            color: "white",
            "& .MuiCardHeader-subheader": {
              color: "rgba(255,255,255,0.8)",
            },
          }}
        />

        <form onSubmit={handleSubmit}>
          <CardContent>
            <Paper
              elevation={0}
              sx={{
                p: 3,
                mb: 3,
                bgcolor: "background.default",
              }}
            >
              <Grid container spacing={3}>
                <Grid size={{ xs: 12, md: 6 }}>
                  <TextField
                    fullWidth
                    label="Name"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    error={Boolean(errors.name)}
                    helperText={errors.name?.[0]}
                  />
                </Grid>

                <Grid size={{ xs: 12, md: 6 }}>
                  <TextField
                    fullWidth
                    label="Phone Number"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    error={Boolean(errors.phone)}
                    helperText={errors.phone?.[0]}
                    placeholder="Enter phone number"
                  />
                </Grid>
              </Grid>
            </Paper>

            <Box
              sx={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                mb: 2,
              }}
            >
              <Typography variant="h6" color="primary">
                Addresses{" "}
                {formData.addresses.length > 0 &&
                  `(${formData.addresses.length})`}
              </Typography>

              <Button
                variant="outlined"
                startIcon={<AddIcon />}
                onClick={addAddress}
              >
                Add Address
              </Button>
            </Box>

            {formData.addresses.length === 0 && (
              <Alert severity="warning" sx={{ mb: 2 }}>
                You must add at least one address
              </Alert>
            )}

            <Stack spacing={3}>
              {formData.addresses.map((address, index) => (
                <Paper
                  key={index}
                  elevation={1}
                  sx={{
                    p: 3,
                    border: "1px solid",
                    borderColor: addressErrors[index]
                      ? "error.main"
                      : errors[`addresses.${index}.address`] ||
                          errors[`addresses.${index}.country`] ||
                          errors[`addresses.${index}.city`] ||
                          errors[`addresses.${index}.state`] ||
                          errors[`addresses.${index}.zip_code`]
                        ? "error.main"
                        : "divider",
                  }}
                >
                  <Box
                    sx={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      mb: 2,
                    }}
                  >
                    <Typography variant="subtitle1" fontWeight={600}>
                      Address #{index + 1}
                      <Typography component="span" color="error" sx={{ ml: 1 }}>
                        *
                      </Typography>
                    </Typography>

                    <IconButton
                      color="error"
                      onClick={() => removeAddress(index)}
                      disabled={formData.addresses.length === 1}
                    >
                      <DeleteIcon />
                    </IconButton>
                  </Box>

                  <Grid container spacing={2}>
                    <Grid size={{ xs: 12, sm: 6 }}>
                      <TextField
                        fullWidth
                        label="Address"
                        value={address.address}
                        onChange={(e) =>
                          handleAddressChange(index, "address", e.target.value)
                        }
                        error={hasAddressFieldError(index, "address")}
                        helperText={getAddressFieldError(index, "address")}
                        placeholder="Street address"
                        size="small"
                      />
                    </Grid>

                    <Grid size={{ xs: 12, sm: 6 }}>
                      <TextField
                        fullWidth
                        label="Country"
                        value={address.country}
                        onChange={(e) =>
                          handleAddressChange(index, "country", e.target.value)
                        }
                        error={hasAddressFieldError(index, "country")}
                        helperText={getAddressFieldError(index, "country")}
                        placeholder="Country"
                        size="small"
                      />
                    </Grid>

                    <Grid size={{ xs: 12, sm: 4 }}>
                      <TextField
                        fullWidth
                        label="City"
                        value={address.city}
                        onChange={(e) =>
                          handleAddressChange(index, "city", e.target.value)
                        }
                        error={hasAddressFieldError(index, "city")}
                        helperText={getAddressFieldError(index, "city")}
                        placeholder="City"
                        size="small"
                      />
                    </Grid>

                    <Grid size={{ xs: 12, sm: 4 }}>
                      <TextField
                        fullWidth
                        label="State"
                        value={address.state}
                        onChange={(e) =>
                          handleAddressChange(index, "state", e.target.value)
                        }
                        error={hasAddressFieldError(index, "state")}
                        helperText={getAddressFieldError(index, "state")}
                        placeholder="State"
                        size="small"
                      />
                    </Grid>

                    <Grid size={{ xs: 12, sm: 4 }}>
                      <TextField
                        fullWidth
                        label="Zip Code"
                        value={address.zip_code}
                        onChange={(e) =>
                          handleAddressChange(index, "zip_code", e.target.value)
                        }
                        error={hasAddressFieldError(index, "zip_code")}
                        helperText={getAddressFieldError(index, "zip_code")}
                        placeholder="Zip code"
                        size="small"
                      />
                    </Grid>
                  </Grid>
                </Paper>
              ))}
            </Stack>
          </CardContent>

          <Divider />

          <CardActions
            sx={{
              p: 3,
              justifyContent: "flex-end",
              gap: 2,
            }}
          >
            <Button
              type="submit"
              variant="contained"
              startIcon={<SaveIcon />}
              disabled={formData.addresses.length === 0}
            >
              Update Profile
            </Button>
          </CardActions>
        </form>
      </Card>
    </Container>
  );
};

export default ProfileEdit;
