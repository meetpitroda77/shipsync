import { useState, useContext } from "react";
import { Outlet, Link, useNavigate } from "react-router-dom";
import {
  AppBar,
  Toolbar,
  Container,
  Box,
  Button,
  IconButton,
  Typography,
  Menu,
  MenuItem,
  Avatar,
  Divider,
  Drawer,
  List,
  ListItemButton,
  ListItemText,
  Badge,
  Tooltip,
  Chip,
} from "@mui/material";

import MenuIcon from "@mui/icons-material/Menu";
import AccountCircleIcon from "@mui/icons-material/AccountCircle";
import LocalShippingIcon from "@mui/icons-material/LocalShipping";
import SearchIcon from "@mui/icons-material/Search";
import PaymentsIcon from "@mui/icons-material/Payments";
import PersonIcon from "@mui/icons-material/Person";
import DashboardIcon from "@mui/icons-material/Dashboard";
import AddIcon from "@mui/icons-material/Add";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import PeopleIcon from "@mui/icons-material/People";
import PersonAddIcon from "@mui/icons-material/PersonAdd";
import SubscriptionIcon from "@mui/icons-material/Subscriptions";
import PaymentIcon from "@mui/icons-material/Payment";
import WarningIcon from "@mui/icons-material/Warning";
import logo from "../assets/shipcync.png";
import { AuthContext } from "../context/UserContext";
import { useSubscription } from "../context/SubscriptionContext";
import LoadingSpinner from "../Components/LoadingSpinner";
import { toast } from "react-toastify";

const CustomerLayout = () => {
  const navigate = useNavigate();
  const { user, logout } = useContext(AuthContext);
  const allowedStatuses = ["active", "trialing"];

  const {
    hasSubscription,
    hasActiveSubscription,
    isPastDue,
    isCancelled,
    remainingShipments,
    isPro,
    canCreateShipment,
    cannotCreateReason,
    loading: subscriptionLoading,
    status,
  } = useSubscription();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [profileAnchorEl, setProfileAnchorEl] = useState(null);
  const [recipientAnchorEl, setRecipientAnchorEl] = useState(null);

  const getSubscriptionStatus = () => {
    if (isPastDue)
      return { text: "Past Due", color: "error", icon: <WarningIcon /> };
    if (hasActiveSubscription)
      return { text: "Active", color: "success", icon: null };
    if (isCancelled) return { text: "Cancelled", color: "warning", icon: null };
    return { text: "Inactive", color: "default", icon: null };
  };

  const subscriptionStatus = getSubscriptionStatus();

  const menuItems = [
    {
      label: "Dashboard",
      path: "/customer",
      icon: <DashboardIcon />,
    },
    {
      label: hasSubscription ? "My Subscription" : "Subscribe",
      path: hasSubscription
        ? "/customer/subscription/dashboard"
        : "/customer/subscription/plans",
      icon: hasSubscription ? <SubscriptionIcon /> : <PaymentIcon />,
      badgeColor: subscriptionStatus.color,
    },
    {
      label: "Shipments",
      path: "/customer/shipments",
      icon: <LocalShippingIcon />,
    },
    {
      label: "Create Shipment",
      path: "/customer/shipments/create",
      icon: <AddIcon />,
      disabled: !allowedStatuses.includes(status),
      tooltip: !allowedStatuses.includes(status)
        ? `Cannot create shipment. Subscription status: ${status}`
        : "",
    },
    {
      label: "Track Shipment",
      path: "/customer/shipments/track-shipment",
      icon: <SearchIcon />,
    },
    {
      label: "Payment Shipments",
      path: "/customer/payments",
      icon: <PaymentsIcon />,
    },
  ];

  const recipientMenuItems = [
    {
      label: "All Recipients",
      path: "/customer/recipients",
      icon: <PeopleIcon />,
    },
    {
      label: "Create Recipient",
      path: "/customer/recipients/create",
      icon: <PersonAddIcon />,
    },
  ];

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const handleProfileMenuOpen = (event) => {
    setProfileAnchorEl(event.currentTarget);
  };

  const handleProfileMenuClose = () => {
    setProfileAnchorEl(null);
  };

  const handleRecipientMenuOpen = (event) => {
    setRecipientAnchorEl(event.currentTarget);
  };

  const handleRecipientMenuClose = () => {
    setRecipientAnchorEl(null);
  };

  const [loading, setLoading] = useState(false);

  const handleLogout = async () => {
    try {
      setLoading(true);
      await logout();
      navigate("/login", { replace: true });
      toast.success("Logged out successfully");
    } catch (error) {
      console.error("Logout failed:", error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner />;
  return (
    <>
      <AppBar
        position="sticky"
        elevation={1}
        sx={{
          bgcolor: "white",
          color: "black",
        }}
      >
        <Container maxWidth="xl">
          <Toolbar sx={{ justifyContent: "space-between" }}>
            <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
              <IconButton
                edge="start"
                sx={{ display: { md: "none" } }}
                onClick={handleDrawerToggle}
              >
                <MenuIcon />
              </IconButton>
            </Box>

            <Box>
              <Link to="/customer">
                <img src={logo} alt="Logo" width="150" />
              </Link>
            </Box>

            <Box
              sx={{
                display: { xs: "none", md: "flex" },
                gap: 1,
                alignItems: "center",
              }}
            >
              {menuItems.map((item) => (
                <Tooltip key={item.label} title={item.tooltip || ""} arrow>
                  <span>
                    <Button
                      component={Link}
                      to={item.path}
                      color="inherit"
                      startIcon={item.icon}
                      disabled={item.disabled}
                      sx={{
                        textTransform: "none",
                        fontWeight: 500,
                        opacity: item.disabled ? 0.5 : 1,
                      }}
                    >
                      {item.label}
                      {item.badge && (
                        <Badge
                          badgeContent={item.badge}
                          color={item.badgeColor}
                          sx={{ ml: 1 }}
                        />
                      )}
                    </Button>
                  </span>
                </Tooltip>
              ))}

              <Button
                color="inherit"
                startIcon={<PersonIcon />}
                endIcon={<ExpandMoreIcon />}
                onClick={handleRecipientMenuOpen}
                sx={{
                  textTransform: "none",
                  fontWeight: 500,
                }}
              >
                Recipients
              </Button>
              <Menu
                anchorEl={recipientAnchorEl}
                open={Boolean(recipientAnchorEl)}
                onClose={handleRecipientMenuClose}
                anchorOrigin={{
                  vertical: "bottom",
                  horizontal: "left",
                }}
                transformOrigin={{
                  vertical: "top",
                  horizontal: "left",
                }}
              >
                {recipientMenuItems.map((item) => (
                  <MenuItem
                    key={item.label}
                    component={Link}
                    to={item.path}
                    onClick={handleRecipientMenuClose}
                    sx={{ gap: 1 }}
                  >
                    {item.icon}
                    {item.label}
                  </MenuItem>
                ))}
              </Menu>
            </Box>

            <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
              {hasActiveSubscription && !isPastDue && (
                <Tooltip
                  title={
                    isPro
                      ? "Pro Plan - Unlimited shipments"
                      : `Basic Plan - ${remainingShipments} shipment${
                          remainingShipments !== 1 ? "s" : ""
                        } remaining`
                  }
                  arrow
                >
                  <Chip
                    label={isPro ? "PRO" : "BASIC"}
                    color={isPro ? "secondary" : "primary"}
                    size="small"
                    sx={{ display: { xs: "none", sm: "flex" } }}
                  />
                </Tooltip>
              )}

              {isPastDue && (
                <Tooltip
                  title="Payment overdue. Please update your payment method to continue using shipment services."
                  arrow
                >
                  <Chip
                    label="Past Due"
                    color="error"
                    size="small"
                    icon={<WarningIcon />}
                    sx={{ display: { xs: "none", sm: "flex" } }}
                  />
                </Tooltip>
              )}

              <IconButton onClick={handleProfileMenuOpen}>
                <Avatar>
                  <AccountCircleIcon />
                </Avatar>
              </IconButton>

              <Menu
                anchorEl={profileAnchorEl}
                open={Boolean(profileAnchorEl)}
                onClose={handleProfileMenuClose}
              >
                <MenuItem disabled>
                  <Typography variant="body2">
                    {user?.role || "Customer"}
                  </Typography>
                </MenuItem>

                <Divider />
                <MenuItem disabled sx={{ opacity: 1 }}>
                  <Box>
                    <Typography variant="caption" color="text.secondary">
                      Subscription Status
                    </Typography>
                    <Typography variant="body2" fontWeight="bold">
                      {hasActiveSubscription
                        ? "Active"
                        : isPastDue
                          ? "Past Due"
                          : isCancelled
                            ? "Cancelled"
                            : "No Subscription"}
                    </Typography>
                  </Box>
                </MenuItem>

                <Divider />

                <MenuItem
                  component={Link}
                  to={"/customer/profile/edit"}
                  onClick={handleProfileMenuClose}
                >
                  Profile
                </MenuItem>

                <MenuItem
                  onClick={() => {
                    handleProfileMenuClose();
                    handleLogout();
                  }}
                >
                  Logout
                </MenuItem>
              </Menu>
            </Box>
          </Toolbar>
        </Container>
      </AppBar>

      <Drawer anchor="left" open={mobileOpen} onClose={handleDrawerToggle}>
        <Box sx={{ width: 280 }}>
          <List>
            {menuItems.map((item) => (
              <ListItemButton
                key={item.label}
                component={Link}
                to={item.path}
                onClick={handleDrawerToggle}
                disabled={item.disabled}
                sx={{ opacity: item.disabled ? 0.5 : 1 }}
              >
                {item.icon}
                <ListItemText primary={item.label} sx={{ ml: 2 }} />
                {item.badge && (
                  <Badge badgeContent={item.badge} color={item.badgeColor} />
                )}
              </ListItemButton>
            ))}
            <Divider />
            <ListItemButton
              component={Link}
              to="/customer/recipients"
              onClick={handleDrawerToggle}
            >
              <PeopleIcon />
              <ListItemText primary="All Recipients" sx={{ ml: 2 }} />
            </ListItemButton>
            <ListItemButton
              component={Link}
              to="/customer/recipients/create"
              onClick={handleDrawerToggle}
            >
              <PersonAddIcon />
              <ListItemText primary="Create Recipient" sx={{ ml: 2 }} />
            </ListItemButton>
          </List>
        </Box>
      </Drawer>

      <Box
        component="main"
        sx={{
          minHeight: "calc(100vh - 64px)",
          bgcolor: "#f8fafc",
          py: 4,
        }}
      >
        <Container maxWidth="xl">
          <Outlet />
        </Container>
      </Box>
    </>
  );
};

export default CustomerLayout;
