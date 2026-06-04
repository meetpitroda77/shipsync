import { useContext, useState } from "react";
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
} from "@mui/material";
import { toast } from "react-toastify";

import AddIcon from "@mui/icons-material/Add";
import DeleteIcon from "@mui/icons-material/Delete";
import SaveIcon from "@mui/icons-material/Save";
import recipentService from "../../services/recipentService";
import { AuthContext } from "../../context/UserContext";
import { useNavigate } from "react-router-dom";

const CreateRecipient = () => {
  const navigate = useNavigate();

  const { user } = useContext(AuthContext);

  const [formData, setFormData] = useState({
    receiver_name: "",
    receiver_phone: "",
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

  const [errors, setErrors] = useState({});
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
    if (formData.addresses.length === 1) return;

    const updatedAddresses = formData.addresses.filter((_, i) => i !== index);

    setFormData((prev) => ({
      ...prev,
      addresses: updatedAddresses,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const res = await recipentService.createRecipent(formData);

      if (res.success) {
        toast.success(res.message);
        navigate(`/${user?.role}/recipients`);
      }
    } catch (error) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors);
      }
    }
  };

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      <Card elevation={3}>
        <CardHeader
          title="Add Recipient"
          subheader="Fill recipient details and addresses"
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
                    label="Receiver Name"
                    name="receiver_name"
                    value={formData.receiver_name}
                    onChange={handleChange}
                    error={Boolean(errors.receiver_name)}
                    helperText={errors.receiver_name?.[0]}
                  />
                </Grid>

                <Grid size={{ xs: 12, md: 6 }}>
                  <TextField
                    fullWidth
                    label="Phone Number"
                    name="receiver_phone"
                    value={formData.receiver_phone}
                    onChange={handleChange}
                    error={Boolean(errors.receiver_phone)}
                    helperText={errors.receiver_phone?.[0]}
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
                Addresses
              </Typography>

              <Button
                variant="outlined"
                startIcon={<AddIcon />}
                onClick={addAddress}
              >
                Add Address
              </Button>
            </Box>

            <Stack spacing={3}>
              {formData.addresses.map((address, index) => (
                <Paper
                  key={index}
                  elevation={1}
                  sx={{
                    p: 3,
                    border: "1px solid",
                    borderColor:
                      errors[`addresses.${index}.address`] ||
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
                    </Typography>

                    {formData.addresses.length > 1 && (
                      <IconButton
                        color="error"
                        onClick={() => removeAddress(index)}
                      >
                        <DeleteIcon />
                      </IconButton>
                    )}
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
                        error={Boolean(errors[`addresses.${index}.address`])}
                        helperText={errors[`addresses.${index}.address`]?.[0]}
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
                        error={Boolean(errors[`addresses.${index}.country`])}
                        helperText={errors[`addresses.${index}.country`]?.[0]}
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
                        error={Boolean(errors[`addresses.${index}.city`])}
                        helperText={errors[`addresses.${index}.city`]?.[0]}
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
                        error={Boolean(errors[`addresses.${index}.state`])}
                        helperText={errors[`addresses.${index}.state`]?.[0]}
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
                        error={Boolean(errors[`addresses.${index}.zip_code`])}
                        helperText={errors[`addresses.${index}.zip_code`]?.[0]}
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
            <Button type="submit" variant="contained" startIcon={<SaveIcon />}>
              Add Recipient
            </Button>
          </CardActions>
        </form>
      </Card>
    </Container>
  );
};

export default CreateRecipient;
