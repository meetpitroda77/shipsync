import React, { useState, useEffect, useContext } from "react";
import { Outlet, useNavigate, useLocation } from "react-router-dom";
import {
  AppBar,
  Box,
  CssBaseline,
  Drawer,
  IconButton,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Toolbar,
  Typography,
  Avatar,
  Menu,
  MenuItem,
  Divider,
  useTheme,
  Collapse,
  Stack,
} from "@mui/material";

import {
  Menu as MenuIcon,
  Dashboard,
  LocalShipping,
  Payments,
  People,
  Person,
  Settings,
  ExitToApp,
  Add,
  ExpandLess,
  ExpandMore,
  ListAlt,
  Report,
} from "@mui/icons-material";
import NotificationBell from "../Pages/NotificationBell";
import { AuthContext } from "../context/UserContext";
import GroupIcon from "@mui/icons-material/Group";
import LoadingSpinner from "../Components/LoadingSpinner";
import { toast } from "react-toastify";
const DRAWER_WIDTH = 280;
const MOBILE_DRAWER_WIDTH = 260;

const AdminLayout = () => {
  const theme = useTheme();
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useContext(AuthContext);
  const [loading, setLoading] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [anchorEl, setAnchorEl] = useState(null);
  const [openShipments, setOpenShipments] = useState(false);
  const [openRecipients, setOpenRecipients] = useState(false);

  useEffect(() => {
    setOpenShipments(location.pathname.includes("/shipments"));
    setOpenRecipients(location.pathname.includes("/recipients"));
  }, [location.pathname]);

  const handleDrawerToggle = () => {
    setMobileOpen((prev) => !prev);
  };

  const handleMenuOpen = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
  };

  const handleNavigation = (path) => {
    navigate(path);
    setMobileOpen(false);
  };

  const handleLogout = async () => {
    try {
      setLoading(true);

      await logout();
      handleMenuClose();
      navigate("/login", { replace: true });
      toast.success("Logged out successfully");
    } catch (error) {
      console.error("Logout failed:", error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner />;
  const isActiveRoute = (path, exact = false) => {
    return exact
      ? location.pathname === path
      : location.pathname.startsWith(path);
  };

  const menuItems = [
    {
      text: "Dashboard",
      icon: <Dashboard />,
      path: "/admin",
      exact: true,
    },
    {
      text: "Shipments",
      icon: <LocalShipping />,
      open: openShipments,
      setOpen: setOpenShipments,
      children: [
        {
          text: "All Shipments",
          icon: <ListAlt />,
          path: "/admin/shipments",
        },
        {
          text: "Create Shipment",
          icon: <Add />,
          path: "/admin/shipments/create",
        },
      ],
    },
    {
      text: "Payments",
      icon: <Payments />,
      path: "/admin/payments",
    },
    {
      text: "Recipients",
      icon: <People />,
      open: openRecipients,
      setOpen: setOpenRecipients,
      children: [
        {
          text: "All Recipients",
          icon: <ListAlt />,
          path: "/admin/recipients",
        },
        {
          text: "Create Recipient",
          icon: <Add />,
          path: "/admin/recipients/create",
        },
      ],
    },
    {
      text: "Profile",
      icon: <Person />,
      path: "/admin/profile/edit",
    },
    {
      text: "Daily Report",
      icon: <Report />,
      path: "/admin/daily-report",
    },
    {
      text: "Report",
      icon: <Report />,
      path: "/admin/reports",
    },
    {
      text: "Users",
      icon: <GroupIcon />,
      path: "/admin/users",
    },
    {
      text: "Settings",
      icon: <Settings />,
      path: "/admin/settings",
    },
  ];

  const drawer = (
    <Box
      sx={{
        height: "100%",
        display: "flex",
        flexDirection: "column",
        bgcolor: "background.paper",
      }}
    >
      <Toolbar
        sx={{
          height: 72,
          minHeight: "72px !important",
          justifyContent: "center",
          borderBottom: `1px solid ${theme.palette.divider}`,
          px: 2,
        }}
      >
        <Stack direction="row" alignItems="center" spacing={1}>
          <LocalShipping
            sx={{
              color: "primary.main",
              fontSize: { xs: 24, sm: 28 },
            }}
          />
          <Typography
            sx={{
              fontWeight: "bold",
              color: "primary.main",
              fontSize: {
                xs: "1rem",
                sm: "1.15rem",
              },
            }}
          >
            Admin Portal
          </Typography>
        </Stack>
      </Toolbar>

      <List sx={{ flexGrow: 1, px: 2, py: 2 }}>
        {menuItems.map((item) => {
          if (item.children) {
            return (
              <React.Fragment key={item.text}>
                <ListItem disablePadding>
                  <ListItemButton
                    onClick={() => item.setOpen(!item.open)}
                    sx={{
                      borderRadius: 2,
                      mb: 0.5,
                    }}
                  >
                    <ListItemIcon
                      sx={{
                        minWidth: 40,
                        color: "text.secondary",
                      }}
                    >
                      {item.icon}
                    </ListItemIcon>

                    <ListItemText
                      primary={item.text}
                      primaryTypographyProps={{
                        fontSize: "0.9rem",
                        fontWeight: 500,
                      }}
                    />

                    {item.open ? <ExpandLess /> : <ExpandMore />}
                  </ListItemButton>
                </ListItem>

                <Collapse in={item.open} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ pl: 3 }}>
                    {item.children.map((child) => (
                      <ListItem key={child.text} disablePadding>
                        <ListItemButton
                          selected={location.pathname === child.path}
                          onClick={() => handleNavigation(child.path)}
                          sx={{
                            borderRadius: 2,
                            mb: 0.5,

                            "&:hover": {
                              bgcolor: "action.hover",
                            },

                            "&.Mui-selected": {
                              bgcolor: "primary.main",
                              color: "white",

                              "& .MuiListItemIcon-root": {
                                color: "white",
                              },
                            },

                            "&.Mui-selected:hover": {
                              bgcolor: "primary.main",
                            },
                          }}
                        >
                          <ListItemIcon
                            sx={{
                              minWidth: 35,
                              color: "text.secondary",
                            }}
                          >
                            {child.icon}
                          </ListItemIcon>

                          <ListItemText
                            primary={child.text}
                            primaryTypographyProps={{
                              fontSize: "0.85rem",
                            }}
                          />
                        </ListItemButton>
                      </ListItem>
                    ))}
                  </List>
                </Collapse>
              </React.Fragment>
            );
          }

          const isActive = isActiveRoute(item.path, item.exact);

          return (
            <ListItem key={item.text} disablePadding>
              <ListItemButton
                selected={isActive}
                onClick={() => handleNavigation(item.path)}
                sx={{
                  borderRadius: 2,
                  mb: 0.5,

                  "&:hover": {
                    bgcolor: "action.hover",
                  },

                  "&.Mui-selected": {
                    bgcolor: "primary.main",
                    color: "white",

                    "& .MuiListItemIcon-root": {
                      color: "white",
                    },
                  },

                  "&.Mui-selected:hover": {
                    bgcolor: "primary.main",
                  },
                }}
              >
                <ListItemIcon
                  sx={{
                    minWidth: 40,
                    color: "text.secondary",
                  }}
                >
                  {item.icon}
                </ListItemIcon>

                <ListItemText
                  primary={item.text}
                  primaryTypographyProps={{
                    fontSize: "0.9rem",
                    fontWeight: 500,
                  }}
                />
              </ListItemButton>
            </ListItem>
          );
        })}
      </List>

      <Divider />

      <List sx={{ px: 2, py: 2 }}>
        <ListItem disablePadding>
          <ListItemButton onClick={handleLogout} sx={{ borderRadius: 2 }}>
            <ListItemIcon sx={{ minWidth: 40 }}>
              <ExitToApp />
            </ListItemIcon>
            <ListItemText primary="Logout" />
          </ListItemButton>
        </ListItem>
      </List>
    </Box>
  );

  return (
    <Box sx={{ display: "flex", minHeight: "100vh" }}>
      <CssBaseline />

      <AppBar
        position="fixed"
        sx={{
          width: {
            xs: "100%",
            md: `calc(100% - ${DRAWER_WIDTH}px)`,
          },
          ml: {
            xs: 0,
            md: `${DRAWER_WIDTH}px`,
          },
          bgcolor: "background.paper",
          color: "text.primary",
          boxShadow: 0,
        }}
      >
        <Toolbar
          sx={{
            height: 72,
            minHeight: "72px !important",
            px: { xs: 2, sm: 3 },
            borderBottom: `1px solid ${theme.palette.divider}`,
          }}
        >
          <IconButton
            onClick={handleDrawerToggle}
            edge="start"
            sx={{
              display: { md: "none" },
              mr: 1,
            }}
          >
            <MenuIcon />
          </IconButton>

          <Box sx={{ flexGrow: 1 }}>
            <Typography
              sx={{
                fontWeight: 600,
                fontSize: {
                  xs: "0.95rem",
                  sm: "1.1rem",
                  md: "1.25rem",
                },
              }}
            >
              Welcome back, Admin
            </Typography>
          </Box>

          <NotificationBell userId={user?.id} role={user?.role} />

          <IconButton onClick={handleMenuOpen} sx={{ ml: 1 }}>
            <Avatar
              sx={{
                width: { xs: 36, sm: 40 },
                height: { xs: 36, sm: 40 },
                border: `2px solid ${theme.palette.primary.main}`,
              }}
            />
          </IconButton>

          <Menu
            anchorEl={anchorEl}
            open={Boolean(anchorEl)}
            onClose={handleMenuClose}
            PaperProps={{
              sx: {
                width: 200,
                borderRadius: 2,
              },
            }}
          >
            <MenuItem
              onClick={() => {
                handleMenuClose();
                navigate("/admin/profile/edit");
              }}
            >
              <ListItemIcon>
                <Person fontSize="small" />
              </ListItemIcon>
              Profile
            </MenuItem>

            <MenuItem
              onClick={() => {
                handleMenuClose();
                navigate("/admin/settings");
              }}
            >
              <ListItemIcon>
                <Settings fontSize="small" />
              </ListItemIcon>
              Settings
            </MenuItem>

            <Divider />

            <MenuItem onClick={handleLogout}>
              <ListItemIcon>
                <ExitToApp fontSize="small" />
              </ListItemIcon>
              Logout
            </MenuItem>
          </Menu>
        </Toolbar>
      </AppBar>

      <Box
        component="nav"
        sx={{
          width: {
            md: DRAWER_WIDTH,
          },
          flexShrink: {
            md: 0,
          },
        }}
      >
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={handleDrawerToggle}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: {
              xs: "block",
              md: "none",
            },
            "& .MuiDrawer-paper": {
              width: MOBILE_DRAWER_WIDTH,
              boxSizing: "border-box",
            },
          }}
        >
          {drawer}
        </Drawer>

        <Drawer
          variant="permanent"
          open
          sx={{
            display: {
              xs: "none",
              md: "block",
            },
            "& .MuiDrawer-paper": {
              width: DRAWER_WIDTH,
              boxSizing: "border-box",
              borderRight: `1px solid ${theme.palette.divider}`,
            },
          }}
        >
          {drawer}
        </Drawer>
      </Box>

      <Box
        component="main"
        sx={{
          flexGrow: 1,
          width: {
            xs: "100%",
            md: `calc(100% - ${DRAWER_WIDTH}px)`,
          },
          minHeight: "100vh",
          bgcolor: "background.default",
          p: {
            xs: 1.5,
            sm: 2,
            md: 3,
          },
        }}
      >
        <Toolbar />
        <Outlet />
      </Box>
    </Box>
  );
};

export default AdminLayout;
