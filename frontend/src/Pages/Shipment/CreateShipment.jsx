import { useState, useEffect, useContext } from "react";
import {
  Box,
  Button,
  Container,
  FormControl,
  FormHelperText,
  IconButton,
  InputLabel,
  MenuItem,
  Paper,
  Select,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Typography,
  Alert,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  Divider,
} from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import PhotoCameraIcon from "@mui/icons-material/PhotoCamera";
import recipentService from "../../services/recipentService";
import shipmentService from "../../services/shipmentService";
import { AuthContext } from "../../context/UserContext";
import LoadingSpinner from "../../Components/LoadingSpinner";
import CloseIcon from "@mui/icons-material/Close";
import PhoneOutlinedIcon from "@mui/icons-material/PhoneOutlined";
import LocationOnOutlinedIcon from "@mui/icons-material/LocationOnOutlined";
import PersonIcon from "@mui/icons-material/Person";
import settingsService from "../../services/settingsService";
const CreateShipment = () => {
  const { user, loading: authLoading } = useContext(AuthContext);

  const [senderAddresses, setSenderAddresses] = useState(user?.addresses || []);
  const [loadingRecipients, setLoadingRecipients] = useState(false);
  const [recipients, setRecipients] = useState([]);
  const [pricePerKg, setPricePerKg] = useState(0);
  const [shippingInsurance, setShippingInsurance] = useState(0);
  const [taxPercent, setTaxPercent] = useState(0);
  const [previewOpen, setPreviewOpen] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [validationLoading, setValidationLoading] = useState(false);

  const [selectedFiles, setSelectedFiles] = useState([]);
  const [imagePreviews, setImagePreviews] = useState([]);
  const [imageErrors, setImageErrors] = useState([]);
  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const response = await settingsService.getServices();

        setPricePerKg(
          Number(
            response.data.find((s) => s.key === "price_per_kg")?.value ?? 0,
          ),
        );

        setShippingInsurance(
          Number(response.data.find((s) => s.key === "insurance")?.value ?? 0),
        );

        setTaxPercent(
          Number(
            response.data.find((s) => s.key === "tax_percent")?.value ?? 0,
          ),
        );
      } catch (error) {
        console.error(error);
      }
    };

    fetchSettings();
  }, []);
  const [packages, setPackages] = useState([
    {
      amount: 1,
      description: "",
      weight: "",
      length: "",
      height: "",
      width: "",
      notes: "",
    },
  ]);
  const [formData, setFormData] = useState({
    sender_name: "",
    sender_phone: "",
    sender_address_id: "",
    receiver_name: "",
    receiver_phone: "",
    receiver_address_id: "",
    package_type: "",
    notes: "",
    courier_company: "",
    shipping_mode: "",
    delivery_method: "",
    estimated_delivery_date: "",
  });
  const [errors, setErrors] = useState({});
  const [subtotal, setSubtotal] = useState(0);
  const [tax, setTax] = useState(0);
  const [total, setTotal] = useState(0);
  const [estimatedCost, setEstimatedCost] = useState("");
  const [estimatedDelivery, setEstimatedDelivery] = useState("");
  const [loading, setLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState("");

  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  const MAX_FILES = 10;
  const ALLOWED_TYPES = [
    "image/jpeg",
    "image/png",
    "image/jpg",
    "image/gif",
    "image/webp",
  ];

  useEffect(() => {
    if (user) {
      setFormData((prev) => ({
        ...prev,
        sender_name: user.name,
        sender_phone: user.phone,
      }));
      setSenderAddresses(user.addresses || []);
    }
  }, [user]);

  const fetchRecipients = async () => {
    setLoadingRecipients(true);
    try {
      const response = await recipentService.getAllRecipientsWithAddresses();
      if (response.success) {
        setRecipients(response.data);
      }
    } catch (error) {
      console.error("Error fetching recipients:", error);
    }
    setLoadingRecipients(false);
  };

  useEffect(() => {
    fetchRecipients();
  }, []);

  const handleRecipientChange = (recipientId) => {
    const selectedRecipient = recipients.find(
      (r) => r.id === parseInt(recipientId),
    );

    if (selectedRecipient) {
      setFormData((prev) => ({
        ...prev,
        receiver_name: recipientId,
        receiver_phone: selectedRecipient.receiver_phone,
        receiver_address_id: "",
      }));
      setErrors((prev) => ({
        ...prev,
        receiver_name: "",
        receiver_phone: "",
        receiver_address_id: "",
      }));
    } else {
      setFormData((prev) => ({
        ...prev,
        receiver_name: "",
        receiver_phone: "",
        receiver_address_id: "",
      }));
    }
  };

  const calculateCosts = () => {
    let subtotalAmount = 0;

    packages.forEach((pkg) => {
      const qty = parseFloat(pkg.amount) || 1;
      const weight = parseFloat(pkg.weight) || 0;
      const length = parseFloat(pkg.length) || 0;
      const width = parseFloat(pkg.width) || 0;
      const height = parseFloat(pkg.height) || 0;

      const volWeight = (length * width * height) / 5000;
      const chargeableWeight = Math.max(weight, volWeight);
      const packageCost = chargeableWeight * pricePerKg * qty;

      subtotalAmount += packageCost;
    });
    const insurance = shippingInsurance;
    const taxAmount = subtotalAmount * taxPercent;
    let totalAmount = subtotalAmount + insurance + taxAmount;

    const deliveryMethod = formData.delivery_method;
    if (deliveryMethod && subtotalAmount > 0) {
      const multiplier = deliveryMethod === "express" ? 1.5 : 1;
      totalAmount = totalAmount * multiplier;
      setEstimatedCost(totalAmount.toFixed(2));

      const days = deliveryMethod === "express" ? 2 : 5;
      const date = new Date();
      date.setDate(date.getDate() + days);
      const formattedDate = `${date.getDate().toString().padStart(2, "0")}-${(
        date.getMonth() + 1
      )
        .toString()
        .padStart(2, "0")}-${date.getFullYear()} (${days} Days)`;
      setEstimatedDelivery(formattedDate);
      setFormData((prev) => ({
        ...prev,
        estimated_delivery_date: `${date.getFullYear()}-${(date.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`,
      }));
    } else {
      setEstimatedCost("");
      setEstimatedDelivery("");
      setFormData((prev) => ({ ...prev, estimated_delivery_date: "" }));
    }

    setSubtotal(subtotalAmount.toFixed(2));
    setTax(taxAmount.toFixed(2));
    setTotal(totalAmount.toFixed(2));
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;

    if (name === "receiver_name") {
      handleRecipientChange(value);
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }

    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: "" }));
    }
  };

  const handlePackageChange = (index, field, value) => {
    const updatedPackages = [...packages];
    updatedPackages[index][field] = value;
    setPackages(updatedPackages);

    const errorKey = `package_${field}_${index}`;
    if (errors[errorKey]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[errorKey];
        return newErrors;
      });
    }

    if (errors[field]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const addPackage = () => {
    setPackages([
      ...packages,
      {
        amount: 1,
        description: "",
        weight: "",
        length: "",
        height: "",
        width: "",
        notes: "",
      },
    ]);
  };

  const removePackage = (index) => {
    if (packages.length > 1) {
      const updatedPackages = packages.filter((_, i) => i !== index);
      setPackages(updatedPackages);
    }
  };

  const validateImageFile = (file) => {
    const errors = [];

    if (!ALLOWED_TYPES.includes(file.type)) {
      errors.push(`File type not allowed. Allowed types: JPEG, PNG, GIF, WEBP`);
    }

    if (file.size > MAX_FILE_SIZE) {
      errors.push(`File size exceeds ${MAX_FILE_SIZE / 1024 / 1024}MB limit`);
    }

    return errors;
  };

  const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    const currentTotal = selectedFiles.length;

    if (currentTotal + files.length > MAX_FILES) {
      setImageErrors((prev) => [
        ...prev,
        `Maximum ${MAX_FILES} images allowed. You can only add ${MAX_FILES - currentTotal} more.`,
      ]);
      return;
    }

    const validFiles = [];
    const newPreviews = [];
    const newErrors = [];

    files.forEach((file) => {
      const fileValidationErrors = validateImageFile(file);

      if (fileValidationErrors.length === 0) {
        validFiles.push(file);
        newPreviews.push(URL.createObjectURL(file));
      } else {
        newErrors.push(`${file.name}: ${fileValidationErrors.join(", ")}`);
      }
    });

    if (validFiles.length > 0) {
      setSelectedFiles((prev) => [...prev, ...validFiles]);
      setImagePreviews((prev) => [...prev, ...newPreviews]);
    }

    if (newErrors.length > 0) {
      setImageErrors((prev) => [...prev, ...newErrors]);
      setTimeout(() => {
        setImageErrors((prev) =>
          prev.filter((err) => !newErrors.includes(err)),
        );
      }, 5000);
    }

    if (validFiles.length > 0 && errors.package_photos) {
      setErrors((prev) => ({ ...prev, package_photos: "" }));
    }

    e.target.value = "";
  };

  const removeImage = (index) => {
    URL.revokeObjectURL(imagePreviews[index]);

    const newFiles = selectedFiles.filter((_, i) => i !== index);
    const newPreviews = imagePreviews.filter((_, i) => i !== index);

    setSelectedFiles(newFiles);
    setImagePreviews(newPreviews);

    if (errors.package_photos && newFiles.length > 0) {
      setErrors((prev) => ({ ...prev, package_photos: "" }));
    }
  };

  const removeAllImages = () => {
    imagePreviews.forEach((preview) => URL.revokeObjectURL(preview));
    setSelectedFiles([]);
    setImagePreviews([]);
    setImageErrors([]);
  };

  const handleValidateAndPreview = async () => {
    setValidationLoading(true);
    setErrors({});

    const formDataToSend = new FormData();

    Object.keys(formData).forEach((key) => {
      if (formData[key]) {
        formDataToSend.append(key, formData[key]);
      }
    });

    packages.forEach((pkg) => {
      formDataToSend.append("package_amount[]", pkg.amount);
      formDataToSend.append("package_description[]", pkg.description);
      formDataToSend.append("package_weight[]", pkg.weight);
      formDataToSend.append("package_length[]", pkg.length);
      formDataToSend.append("package_height[]", pkg.height);
      formDataToSend.append("package_width[]", pkg.width);

      if (pkg.notes) {
        formDataToSend.append("package_notes[]", pkg.notes);
      }
    });

    if (selectedFiles.length > 0) {
      selectedFiles.forEach((file) => {
        formDataToSend.append("package_photos[]", file);
      });
    }

    try {
      const response = await shipmentService.validateShipment(formDataToSend);
      console.log("Validation response:", response);

      if (response.success) {
        setPreviewData(response.preview);
        setPreviewOpen(true);
      }
    } catch (error) {
      console.error("Validation error:", error);

      if (error.response?.data?.errors) {
        const newErrors = {};

        error.response.data.errors.forEach((err) => {
          newErrors[err.field] = err.message;
        });

        setErrors(newErrors);

        const firstErrorField = Object.keys(newErrors)[0];

        if (firstErrorField) {
          const element = document.querySelector(`[name="${firstErrorField}"]`);

          if (element) {
            element.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });

            element.focus();
          }
        }
      } else {
        setErrors({
          general:
            error.response?.data?.message ||
            "Validation failed. Please check your inputs.",
        });
      }
    } finally {
      setValidationLoading(false);
    }
  };

  const handleConfirmAndPay = async () => {
    setPreviewOpen(false);
    setLoading(true);

    const formDataToSend = new FormData();

    Object.keys(formData).forEach((key) => {
      if (formData[key]) {
        formDataToSend.append(key, formData[key]);
      }
    });

    packages.forEach((pkg, index) => {
      formDataToSend.append(`package_amount[]`, pkg.amount);
      formDataToSend.append(`package_description[]`, pkg.description);
      formDataToSend.append(`package_weight[]`, pkg.weight);
      formDataToSend.append(`package_length[]`, pkg.length);
      formDataToSend.append(`package_height[]`, pkg.height);
      formDataToSend.append(`package_width[]`, pkg.width);
      if (pkg.notes) {
        formDataToSend.append(`package_notes[]`, pkg.notes);
      }
    });

    selectedFiles.forEach((file) => {
      formDataToSend.append(`package_photos[]`, file);
    });

    try {
      const response = await shipmentService.createShipment(formDataToSend);

      if (response.success && response.data?.payment_url) {
        window.location.href = response.data.payment_url;
      } else {
        setSuccessMessage("Shipment created successfully!");
        resetForm();
      }
    } catch (error) {
      console.error("Create shipment error:", error);
      setErrors({
        general:
          error.response?.data?.message ||
          "Failed to create shipment. Please try again.",
      });
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setFormData({
      sender_name: user?.name || "",
      sender_phone: user?.phone || "",
      sender_address_id: "",
      receiver_name: "",
      receiver_phone: "",
      receiver_address_id: "",
      package_type: "",
      notes: "",
      courier_company: "",
      shipping_mode: "",
      delivery_method: "",
      estimated_delivery_date: "",
    });
    setPackages([
      {
        amount: 1,
        description: "",
        weight: "",
        length: "",
        height: "",
        width: "",
        notes: "",
      },
    ]);
    removeAllImages();
    setSuccessMessage("");
    setErrors({});
    setImageErrors([]);
    setPreviewData(null);
  };

  useEffect(() => {
    calculateCosts();
  }, [
    packages,
    formData.delivery_method,
    pricePerKg,
    shippingInsurance,
    taxPercent,
  ]);

  if (authLoading) {
    return <LoadingSpinner />;
  }

  return (
    <Box className="app-content">
      <Container maxWidth="lg" sx={{ py: { xs: 1, sm: 2 } }}>
        {successMessage && (
          <Alert
            severity="success"
            sx={{ mb: 2 }}
            onClose={() => setSuccessMessage("")}
          >
            {successMessage}
          </Alert>
        )}

        {errors.general && (
          <Alert
            severity="error"
            sx={{ mb: 2 }}
            onClose={() => setErrors({ ...errors, general: "" })}
          >
            {errors.general}
          </Alert>
        )}

        <Box component="form" encType="multipart/form-data">
          <Stack spacing={3}>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 6 }}>
                <Paper
                  elevation={2}
                  sx={{ p: { xs: 2, sm: 3 }, height: "100%", boxShadow: 5 }}
                >
                  <Typography variant="h6" gutterBottom>
                    Sender Information
                  </Typography>
                  <TextField
                    label="Full Name *"
                    required
                    fullWidth
                    margin="normal"
                    name="sender_name"
                    value={formData.sender_name}
                    onChange={handleInputChange}
                    error={Boolean(errors.sender_name)}
                    helperText={errors.sender_name || " "}
                    InputProps={{ readOnly: true }}
                    disabled
                  />
                  <TextField
                    label="Phone Number"
                    fullWidth
                    margin="normal"
                    name="sender_phone"
                    value={formData.sender_phone}
                    onChange={handleInputChange}
                    error={Boolean(errors.sender_phone)}
                    helperText={errors.sender_phone || " "}
                    InputProps={{ readOnly: true }}
                    disabled
                  />
                  <FormControl
                    fullWidth
                    margin="normal"
                    error={Boolean(errors.sender_address_id)}
                  >
                    <InputLabel id="sender_address_label">Address *</InputLabel>
                    <Select
                      labelId="sender_address_label"
                      label="Address *"
                      name="sender_address_id"
                      value={formData.sender_address_id}
                      onChange={handleInputChange}
                    >
                      <MenuItem value="">Select an Address</MenuItem>
                      {senderAddresses.map((address) => (
                        <MenuItem key={address.id} value={address.id}>
                          {address.address} - {address.city}, {address.state}
                        </MenuItem>
                      ))}
                    </Select>
                    <FormHelperText>
                      {errors.sender_address_id || " "}
                    </FormHelperText>
                  </FormControl>
                </Paper>
              </Grid>

              <Grid size={{ xs: 12, md: 6 }}>
                <Paper
                  elevation={2}
                  sx={{ p: { xs: 2, sm: 3 }, height: "100%", boxShadow: 5 }}
                >
                  <Typography variant="h6" gutterBottom>
                    Receiver Information
                  </Typography>

                  <FormControl
                    fullWidth
                    margin="normal"
                    error={Boolean(errors.receiver_name)}
                  >
                    <InputLabel id="receiver_name_label">
                      Full Name *
                    </InputLabel>
                    <Select
                      labelId="receiver_name_label"
                      label="Full Name *"
                      name="receiver_name"
                      value={formData.receiver_name}
                      onChange={handleInputChange}
                    >
                      <MenuItem value="">Select a Name</MenuItem>
                      {recipients.map((recipient) => (
                        <MenuItem key={recipient.id} value={recipient.id}>
                          {recipient.receiver_name}
                        </MenuItem>
                      ))}
                    </Select>
                    <FormHelperText>
                      {errors.receiver_name || " "}
                    </FormHelperText>
                  </FormControl>

                  <TextField
                    label="Phone Number"
                    fullWidth
                    margin="normal"
                    name="receiver_phone"
                    value={formData.receiver_phone}
                    onChange={handleInputChange}
                    error={Boolean(errors.receiver_phone)}
                    helperText={errors.receiver_phone || " "}
                  />

                  <FormControl
                    fullWidth
                    margin="normal"
                    error={Boolean(errors.receiver_address_id)}
                    sx={{
                      "& .MuiOutlinedInput-root.Mui-disabled .MuiOutlinedInput-notchedOutline":
                        {
                          borderColor: errors.receiver_address_id ? "red" : "",
                        },
                    }}
                  >
                    <InputLabel id="receiver_address_label">
                      Address *
                    </InputLabel>
                    <Select
                      labelId="receiver_address_label"
                      label="Address *"
                      name="receiver_address_id"
                      value={formData.receiver_address_id}
                      onChange={handleInputChange}
                    >
                      <MenuItem value="">Select an Address</MenuItem>
                      {formData.receiver_name &&
                        recipients
                          .find(
                            (r) => r.id === parseInt(formData.receiver_name),
                          )
                          ?.addresses.map((address) => (
                            <MenuItem key={address.id} value={address.id}>
                              {address.address} - {address.city},{" "}
                              {address.state}
                            </MenuItem>
                          ))}
                    </Select>
                    <FormHelperText>
                      {errors.receiver_address_id || " "}
                    </FormHelperText>
                  </FormControl>
                </Paper>
              </Grid>
            </Grid>

            {/* Package Details Section */}
            <Paper elevation={2} sx={{ p: { xs: 2, sm: 3 }, boxShadow: 5 }}>
              <Typography variant="h6" gutterBottom>
                Package Details
              </Typography>

              <Stack spacing={3}>
                {/* Package Type - Full width on all screens */}
                <FormControl fullWidth error={Boolean(errors.package_type)}>
                  <InputLabel id="package_type_label">
                    Package Type *
                  </InputLabel>
                  <Select
                    labelId="package_type_label"
                    label="Package Type *"
                    name="package_type"
                    value={formData.package_type}
                    onChange={handleInputChange}
                  >
                    <MenuItem value="">-- Select Package Type --</MenuItem>
                    <MenuItem value="document">Document</MenuItem>
                    <MenuItem value="parcel">Parcel</MenuItem>
                    <MenuItem value="box">Box</MenuItem>
                    <MenuItem value="crate">Crate</MenuItem>
                    <MenuItem value="pallet">Pallet</MenuItem>
                    <MenuItem value="fragile">Fragile Item</MenuItem>
                    <MenuItem value="liquid">Liquid</MenuItem>
                    <MenuItem value="perishable">Perishable Goods</MenuItem>
                  </Select>
                  <FormHelperText>{errors.package_type || " "}</FormHelperText>
                </FormControl>

                {/* Package Photos Section */}
                <Box>
                  <Typography
                    variant="subtitle1"
                    sx={{ fontWeight: 700, mb: 1.5 }}
                  >
                    Package Photos *
                  </Typography>

                  {errors.package_photos && (
                    <Alert severity="error" sx={{ mb: 2 }}>
                      {errors.package_photos}
                    </Alert>
                  )}

                  {imageErrors.length > 0 && (
                    <Stack spacing={1} sx={{ mb: 2 }}>
                      {imageErrors.map((error, index) => (
                        <Alert
                          key={index}
                          severity="warning"
                          onClose={() => {
                            setImageErrors((prev) =>
                              prev.filter((_, i) => i !== index),
                            );
                          }}
                        >
                          {error}
                        </Alert>
                      ))}
                    </Stack>
                  )}

                  <Paper
                    variant="outlined"
                    sx={{
                      p: { xs: 2, sm: 3 },
                      borderRadius: 3,
                      borderColor: errors.package_photos
                        ? "error.main"
                        : "divider",
                    }}
                  >
                    <Stack
                      direction={{ xs: "column", sm: "row" }}
                      spacing={2}
                      alignItems={{ xs: "flex-start", sm: "center" }}
                      justifyContent="space-between"
                      sx={{ mb: 2.5 }}
                    >
                      <Stack direction="row" spacing={2} alignItems="center">
                        <Box
                          sx={{
                            width: 54,
                            height: 54,
                            borderRadius: 2,
                            bgcolor: "primary.main",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                          }}
                        >
                          <PhotoCameraIcon
                            sx={{ color: "#fff", fontSize: 28 }}
                          />
                        </Box>
                        <Box>
                          <Typography variant="h6" sx={{ fontWeight: 700 }}>
                            Upload Package Images
                          </Typography>
                          <Typography variant="body2" color="text.secondary">
                            JPG, PNG, GIF, WEBP • Max 5MB each
                          </Typography>
                        </Box>
                      </Stack>

                      <Button
                        variant="contained"
                        component="label"
                        startIcon={<PhotoCameraIcon />}
                        sx={{ textTransform: "none", borderRadius: 2 }}
                      >
                        {selectedFiles.length > 0
                          ? "Add Files"
                          : "Choose Files"}
                        <input
                          hidden
                          type="file"
                          multiple
                          accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                          onChange={handleFileSelect}
                        />
                      </Button>
                    </Stack>

                    {selectedFiles.length > 0 && (
                      <Box>
                        <Typography
                          variant="body2"
                          sx={{ mb: 1.5, fontWeight: 600 }}
                        >
                          Selected Files ({selectedFiles.length})
                        </Typography>
                        <Stack spacing={1.2}>
                          {selectedFiles.map((file, index) => (
                            <Paper
                              key={index}
                              variant="outlined"
                              sx={{
                                px: 2,
                                py: 1.5,
                                borderRadius: 2,
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "space-between",
                              }}
                            >
                              <Stack
                                direction="row"
                                spacing={1.5}
                                alignItems="center"
                                sx={{ flex: 1 }}
                              >
                                <Box>
                                  <Typography
                                    variant="body2"
                                    sx={{ fontWeight: 600 }}
                                  >
                                    {file.name}
                                  </Typography>
                                  <Typography
                                    variant="caption"
                                    color="text.secondary"
                                  >
                                    {(file.size / 1024 / 1024).toFixed(2)} MB
                                  </Typography>
                                </Box>
                              </Stack>
                              <IconButton
                                size="small"
                                color="error"
                                onClick={() => removeImage(index)}
                              >
                                <CloseIcon fontSize="small" />
                              </IconButton>
                            </Paper>
                          ))}
                        </Stack>
                        {selectedFiles.length > 1 && (
                          <Button
                            color="error"
                            startIcon={<DeleteIcon />}
                            onClick={removeAllImages}
                            sx={{ mt: 2, textTransform: "none" }}
                          >
                            Remove All Files
                          </Button>
                        )}
                      </Box>
                    )}
                  </Paper>
                </Box>

                {/* Notes - Full width */}
                <TextField
                  label="Notes"
                  multiline
                  rows={3}
                  fullWidth
                  name="notes"
                  value={formData.notes}
                  onChange={handleInputChange}
                  placeholder="Any special instructions for delivery..."
                />

                {/* Courier and Shipping Mode - Responsive grid layout */}
                <Grid container spacing={2}>
                  <Grid size={{ xs: 12, sm: 6 }}>
                    <FormControl
                      fullWidth
                      error={Boolean(errors.courier_company)}
                    >
                      <InputLabel id="courier_company_label">
                        Courier Company *
                      </InputLabel>
                      <Select
                        labelId="courier_company_label"
                        label="Courier Company *"
                        name="courier_company"
                        value={formData.courier_company}
                        onChange={handleInputChange}
                      >
                        <MenuItem value="">Select Courier</MenuItem>
                        <MenuItem value="dhl">DHL</MenuItem>
                        <MenuItem value="fedex">FedEx</MenuItem>
                        <MenuItem value="bluedart">BlueDart</MenuItem>
                        <MenuItem value="delhivery">Delhivery</MenuItem>
                      </Select>
                      <FormHelperText>
                        {errors.courier_company || " "}
                      </FormHelperText>
                    </FormControl>
                  </Grid>
                  <Grid size={{ xs: 12, sm: 6 }}>
                    <FormControl
                      fullWidth
                      error={Boolean(errors.shipping_mode)}
                    >
                      <InputLabel id="shipping_mode_label">
                        Shipping Mode *
                      </InputLabel>
                      <Select
                        labelId="shipping_mode_label"
                        label="Shipping Mode *"
                        name="shipping_mode"
                        value={formData.shipping_mode}
                        onChange={handleInputChange}
                      >
                        <MenuItem value="">Select Mode</MenuItem>
                        <MenuItem value="air">Air</MenuItem>
                        <MenuItem value="surface">Surface</MenuItem>
                        <MenuItem value="rail">Rail</MenuItem>
                      </Select>
                      <FormHelperText>
                        {errors.shipping_mode || " "}
                      </FormHelperText>
                    </FormControl>
                  </Grid>
                </Grid>

                {/* Packages Section */}
                <Box>
                  <Typography variant="subtitle1" gutterBottom>
                    Packages
                  </Typography>
                  <Stack spacing={2}>
                    {packages.map((pkg, index) => (
                      <Paper
                        key={index}
                        variant="outlined"
                        sx={{ p: { xs: 1.5, sm: 2 }, position: "relative" }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            justifyContent: "flex-end",
                            mb: 1,
                          }}
                        >
                          <Button
                            variant="text"
                            color="error"
                            startIcon={<DeleteIcon />}
                            onClick={() => removePackage(index)}
                            disabled={packages.length <= 1}
                          >
                            Remove Package
                          </Button>
                        </Box>

                        <Stack spacing={2}>
                          {/* Quantity and Description - Responsive */}
                          <Grid container spacing={2}>
                            <Grid size={{ xs: 12, sm: 3 }}>
                              <TextField
                                name={`package_amount_${index}`}
                                label="Quantity *"
                                type="number"
                                fullWidth
                                value={pkg.amount}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "amount",
                                    e.target.value,
                                  )
                                }
                                inputProps={{ min: 1 }}
                                error={Boolean(
                                  errors[`package_amount_${index}`],
                                )}
                                helperText={
                                  errors[`package_amount_${index}`] || " "
                                }
                              />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 9 }}>
                              <TextField
                                name={`package_description_${index}`}
                                label="Package Description *"
                                fullWidth
                                value={pkg.description}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "description",
                                    e.target.value,
                                  )
                                }
                                placeholder="e.g., Electronics, Books, etc."
                                error={Boolean(
                                  errors[`package_description_${index}`],
                                )}
                                helperText={
                                  errors[`package_description_${index}`] || " "
                                }
                              />
                            </Grid>
                          </Grid>

                          {/* Dimensions - Responsive grid */}
                          <Grid container spacing={2}>
                            <Grid size={{ xs: 12, sm: 3 }}>
                              <TextField
                                name={`package_weight_${index}`}
                                label="Weight (kg) *"
                                type="number"
                                step={0.01}
                                fullWidth
                                value={pkg.weight}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "weight",
                                    e.target.value,
                                  )
                                }
                                placeholder="0.00"
                                error={Boolean(
                                  errors[`package_weight_${index}`],
                                )}
                                helperText={
                                  errors[`package_weight_${index}`] || " "
                                }
                              />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 3 }}>
                              <TextField
                                name={`package_length_${index}`}
                                label="Length (cm) *"
                                type="number"
                                step={0.01}
                                fullWidth
                                value={pkg.length}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "length",
                                    e.target.value,
                                  )
                                }
                                placeholder="0.00"
                                error={Boolean(
                                  errors[`package_length_${index}`],
                                )}
                                helperText={
                                  errors[`package_length_${index}`] || " "
                                }
                              />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 3 }}>
                              <TextField
                                name={`package_height_${index}`}
                                label="Height (cm) *"
                                type="number"
                                step={0.01}
                                fullWidth
                                value={pkg.height}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "height",
                                    e.target.value,
                                  )
                                }
                                placeholder="0.00"
                                error={Boolean(
                                  errors[`package_height_${index}`],
                                )}
                                helperText={
                                  errors[`package_height_${index}`] || " "
                                }
                              />
                            </Grid>
                            <Grid size={{ xs: 12, sm: 3 }}>
                              <TextField
                                name={`package_width_${index}`}
                                label="Width (cm) *"
                                type="number"
                                step={0.01}
                                fullWidth
                                value={pkg.width}
                                onChange={(e) =>
                                  handlePackageChange(
                                    index,
                                    "width",
                                    e.target.value,
                                  )
                                }
                                placeholder="0.00"
                                error={Boolean(
                                  errors[`package_width_${index}`],
                                )}
                                helperText={
                                  errors[`package_width_${index}`] || " "
                                }
                              />
                            </Grid>
                          </Grid>
                        </Stack>
                      </Paper>
                    ))}
                  </Stack>

                  <Button
                    variant="contained"
                    sx={{ mt: 2 }}
                    onClick={addPackage}
                  >
                    + Add Another Package
                  </Button>
                </Box>

                {/* Delivery Method, Estimated Delivery, Cost - Responsive grid */}
                <Grid container spacing={2}>
                  <Grid size={{ xs: 12, md: 4 }}>
                    <FormControl
                      fullWidth
                      error={Boolean(errors.delivery_method)}
                    >
                      <InputLabel id="delivery_method_label">
                        Delivery Method *
                      </InputLabel>
                      <Select
                        labelId="delivery_method_label"
                        label="Delivery Method *"
                        name="delivery_method"
                        value={formData.delivery_method}
                        onChange={handleInputChange}
                      >
                        <MenuItem value="">Select Delivery Method</MenuItem>
                        <MenuItem value="standard">Standard (5 Days)</MenuItem>
                        <MenuItem value="express">Express (2 Days)</MenuItem>
                      </Select>
                      <FormHelperText>
                        {errors.delivery_method || " "}
                      </FormHelperText>
                    </FormControl>
                  </Grid>
                  <Grid size={{ xs: 12, md: 4 }}>
                    <TextField
                      label="Estimated Delivery"
                      fullWidth
                      value={estimatedDelivery}
                      disabled
                    />
                  </Grid>
                  <Grid size={{ xs: 12, md: 4 }}>
                    <TextField
                      label="Estimated Cost ($)"
                      fullWidth
                      value={estimatedCost}
                      disabled
                    />
                  </Grid>
                </Grid>
              </Stack>

              {/* Cost Summary Table */}
              <Box sx={{ mt: 4 }}>
                <TableContainer component={Paper} variant="outlined">
                  <Table>
                    <TableHead>
                      <TableRow>
                        <TableCell>Description</TableCell>
                        <TableCell align="right">Amount ($)</TableCell>
                      </TableRow>
                    </TableHead>
                    <TableBody>
                      <TableRow>
                        <TableCell>Subtotal</TableCell>
                        <TableCell align="right">${subtotal}</TableCell>
                      </TableRow>

                      <TableRow>
                        <TableCell>Shipping Insurance</TableCell>
                        <TableCell align="right">
                          ${Number(shippingInsurance).toFixed(2)}
                        </TableCell>
                      </TableRow>

                      <TableRow>
                        <TableCell>Tax ({Number(taxPercent) * 100}%)</TableCell>
                        <TableCell align="right">${tax}</TableCell>
                      </TableRow>

                      <TableRow>
                        <TableCell>
                          <Typography fontWeight={700}>Total</Typography>
                        </TableCell>
                        <TableCell align="right">
                          <Typography fontWeight={700}>${total}</Typography>
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </TableContainer>
              </Box>
            </Paper>

            {/* Action Buttons - Responsive */}
            <Grid container spacing={2} justifyContent="center">
              <Grid size={{ xs: 12, sm: "auto" }}>
                <Button
                  variant="outlined"
                  size="large"
                  onClick={resetForm}
                  fullWidth
                  sx={{ minWidth: { xs: "100%", sm: 200 } }}
                >
                  Reset
                </Button>
              </Grid>
              <Grid size={{ xs: 12, sm: "auto" }}>
                <Button
                  variant="contained"
                  size="large"
                  onClick={handleValidateAndPreview}
                  disabled={validationLoading}
                  fullWidth
                  sx={{ minWidth: { xs: "100%", sm: 200 } }}
                >
                  {validationLoading ? "Validating..." : "Preview Shipment"}
                </Button>
              </Grid>
            </Grid>
          </Stack>
        </Box>
      </Container>

      {/* Preview Dialog with responsive sizing */}
      <Dialog
        open={previewOpen}
        onClose={() => setPreviewOpen(false)}
        maxWidth={false}
        PaperProps={{
          sx: {
            width: { xs: "90vw", sm: "80vw", md: "72vw" },
            maxWidth: "1150px",
            borderRadius: 3,
            overflow: "hidden",
            bgcolor: "#ffffff",
          },
        }}
      >
        <DialogTitle
          sx={{
            px: 3,
            py: 2,
            borderBottom: "1px solid #e5e7eb",
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
          }}
        >
          <Typography variant="h5" fontWeight={600}>
            Shipment Preview
          </Typography>

          <IconButton
            onClick={() => setPreviewOpen(false)}
            sx={{
              border: "2px solid #bfdbfe",
              borderRadius: 2,
            }}
          >
            <CloseIcon />
          </IconButton>
        </DialogTitle>

        <DialogContent sx={{ p: 3 }}>
          {previewData && (
            <Stack spacing={3}>
              {/* Sender and Receiver Preview - Responsive */}
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 3,
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                      minHeight: 180,
                    }}
                  >
                    <Typography
                      variant="subtitle2"
                      sx={{
                        mb: 2,
                        color: "text.secondary",
                        letterSpacing: 1,
                      }}
                    >
                      SENDER
                    </Typography>

                    <Stack spacing={3}>
                      <Stack direction="row" spacing={1.5}>
                        <PersonIcon />
                        <Typography fontWeight={700}>
                          {previewData.sender_name}
                        </Typography>
                      </Stack>

                      <Stack direction="row" spacing={1.5}>
                        <PhoneOutlinedIcon />
                        <Typography>{previewData.sender_phone}</Typography>
                      </Stack>

                      <Stack direction="row" spacing={1.5}>
                        <LocationOnOutlinedIcon />
                        <Typography>{previewData.sender_address}</Typography>
                      </Stack>
                    </Stack>
                  </Paper>
                </Grid>

                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 3,
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                      minHeight: 180,
                    }}
                  >
                    <Typography
                      variant="subtitle2"
                      sx={{
                        mb: 2,
                        color: "text.secondary",
                        letterSpacing: 1,
                      }}
                    >
                      RECEIVER
                    </Typography>

                    <Stack spacing={3}>
                      <Stack direction="row" spacing={1.5}>
                        <PersonIcon />
                        <Typography fontWeight={700}>
                          {previewData.receiver_name}
                        </Typography>
                      </Stack>

                      <Stack direction="row" spacing={1.5}>
                        <PhoneOutlinedIcon />
                        <Typography>{previewData.receiver_phone}</Typography>
                      </Stack>

                      <Stack direction="row" spacing={1.5}>
                        <LocationOnOutlinedIcon />
                        <Typography>{previewData.receiver_address}</Typography>
                      </Stack>
                    </Stack>
                  </Paper>
                </Grid>
              </Grid>

              <Divider />

              {/* Summary Cards - Responsive */}
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2.5,
                      textAlign: "center",
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                    }}
                  >
                    <Typography variant="body2" color="text.secondary">
                      Packages
                    </Typography>
                    <Typography variant="h6" fontWeight={500} mt={1}>
                      {previewData.packages?.length || 0}
                    </Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2.5,
                      textAlign: "center",
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                    }}
                  >
                    <Typography variant="body2" color="text.secondary">
                      Total Weight
                    </Typography>
                    <Typography variant="h6" fontWeight={500} mt={1}>
                      {previewData.packages
                        ?.reduce((sum, pkg) => sum + parseFloat(pkg.weight), 0)
                        .toFixed(2)}{" "}
                      kg
                    </Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2.5,
                      textAlign: "center",
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                    }}
                  >
                    <Typography variant="body2" color="text.secondary">
                      Delivery
                    </Typography>
                    <Typography
                      variant="h6"
                      fontWeight={500}
                      mt={1}
                      textTransform="capitalize"
                    >
                      {previewData.delivery_method}
                    </Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, sm: 6, md: 3 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 2.5,
                      textAlign: "center",
                      borderRadius: 2,
                      bgcolor: "#ecfeff",
                    }}
                  >
                    <Typography variant="body2" color="text.secondary">
                      Est Date
                    </Typography>
                    <Typography variant="h6" fontWeight={500} mt={1}>
                      {previewData.delivery_date}
                    </Typography>
                  </Paper>
                </Grid>
              </Grid>

              <Divider />

              <Box>
                <Typography variant="h6" mb={2}>
                  Packages
                </Typography>

                <TableContainer sx={{ overflowX: "auto" }}>
                  <Table size="small">
                    <TableHead>
                      <TableRow sx={{ bgcolor: "#d9f3f8" }}>
                        <TableCell>#</TableCell>
                        <TableCell>Qty</TableCell>
                        <TableCell>Weight</TableCell>
                        <TableCell>Dimensions</TableCell>
                      </TableRow>
                    </TableHead>
                    <TableBody>
                      {previewData.packages?.map((pkg, index) => (
                        <TableRow key={index}>
                          <TableCell>{index + 1}</TableCell>
                          <TableCell>{pkg.qty}</TableCell>
                          <TableCell>{pkg.weight}</TableCell>
                          <TableCell>{pkg.dimensions}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </TableContainer>
              </Box>

              <Divider />

              {/* Cost Breakdown - Responsive */}
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, sm: 4 }}>
                  <Typography align="center" variant="h6">
                    <strong>Subtotal:</strong> ${previewData.subtotal}
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 4 }}>
                  <Typography align="center" variant="h6">
                    <strong>Tax:</strong> ${previewData.tax}
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 4 }}>
                  <Typography align="center" variant="h6">
                    <strong>Total:</strong> ${previewData.total}
                  </Typography>
                </Grid>
              </Grid>
            </Stack>
          )}
        </DialogContent>

        <DialogActions
          sx={{
            px: 3,
            py: 2,
            borderTop: "1px solid #e5e7eb",
            justifyContent: "flex-end",
            gap: 1.5,
          }}
        >
          <Button
            variant="contained"
            color="inherit"
            onClick={() => setPreviewOpen(false)}
            sx={{
              textTransform: "none",
            }}
          >
            Edit
          </Button>
          <Button
            variant="contained"
            color="success"
            onClick={handleConfirmAndPay}
            sx={{
              textTransform: "none",
            }}
          >
            Confirm & Pay
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default CreateShipment;
