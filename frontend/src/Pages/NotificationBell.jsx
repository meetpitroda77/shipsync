import React, { useState, useEffect } from "react";

import {
  IconButton,
  Badge,
  Menu,
  Box,
  Typography,
  List,
  ListItem,
  Divider,
  Button,
  CircularProgress,
  Tooltip,
} from "@mui/material";

import {
  Notifications as NotificationsIcon,
  DoneAll,
  DeleteSweep,
  Circle,
} from "@mui/icons-material";

import notificationService from "../services/notificationService";

const NotificationBell = ({ userId }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    if (!userId) return;

    let mounted = true;

    const init = async () => {
      try {
        const unreadRes = await notificationService.getUnreadCount();

        if (mounted) {
          setUnreadCount(unreadRes.count || 0);
        }
      } catch (error) {
        console.error(error);
      }
    };

    init();

    notificationService.subscribeToNotifications(userId, (notification) => {
      if (!mounted) return;

      setNotifications((prev) => {
        const exists = prev.some((item) => item.id === notification.id);

        if (exists) return prev;

        return [notification, ...prev];
      });

      setUnreadCount((prev) => prev + 1);
    });

    return () => {
      mounted = false;

      notificationService.unsubscribeFromNotifications(userId);
    };
  }, [userId]);

  const loadNotifications = async (reset = false) => {
    setLoading(true);

    try {
      const currentPage = reset ? 1 : page;

      const response = await notificationService.getNotifications(currentPage);

      if (reset) {
        setNotifications((prev) => {
          const merged = [...response.data];

          prev.forEach((item) => {
            const exists = merged.some((n) => n.id === item.id);

            if (!exists) {
              merged.unshift(item);
            }
          });

          return merged;
        });

        setPage(2);
      } else {
        setNotifications((prev) => [...prev, ...response.data]);

        setPage(currentPage + 1);
      }

      setHasMore(response.data.length === 15);
    } catch (error) {
      console.error("Failed to load notifications:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleOpen = async (event) => {
    setAnchorEl(event.currentTarget);

    await loadNotifications(true);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleMarkAsRead = async (notificationId) => {
    try {
      await notificationService.markAsRead(notificationId);

      setNotifications((prev) =>
        prev.map((notif) =>
          notif.id === notificationId
            ? { ...notif, read_at: new Date().toISOString() }
            : notif,
        ),
      );

      setUnreadCount((prev) => Math.max(0, prev - 1));
    } catch (error) {
      console.error("Failed to mark as read:", error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await notificationService.markAllAsRead();

      setNotifications((prev) =>
        prev.map((notif) => ({
          ...notif,
          read_at: new Date().toISOString(),
        })),
      );

      setUnreadCount(0);
    } catch (error) {
      console.error("Failed to mark all as read:", error);
    }
  };

  const handleDeleteAll = async () => {
    try {
      await notificationService.deleteAllNotifications();

      setNotifications([]);
      setUnreadCount(0);
    } catch (error) {
      console.error("Failed to delete all:", error);
    }
  };

  const formatTime = (date) => {
    const now = new Date();
    const notifDate = new Date(date);

    const diffMinutes = Math.floor((now - notifDate) / 60000);

    if (diffMinutes < 1) return "Just now";

    if (diffMinutes < 60) {
      return `${diffMinutes} min ago`;
    }

    if (diffMinutes < 1440) {
      return `${Math.floor(diffMinutes / 60)} hours ago`;
    }

    return notifDate.toLocaleDateString();
  };

  return (
    <>
      <Tooltip title="Notifications">
        <IconButton onClick={handleOpen}>
          <Badge badgeContent={unreadCount} color="error">
            <NotificationsIcon />
          </Badge>
        </IconButton>
      </Tooltip>

      <Menu
        anchorEl={anchorEl}
        open={Boolean(anchorEl)}
        onClose={handleClose}
        keepMounted
        transformOrigin={{
          horizontal: "right",
          vertical: "top",
        }}
        anchorOrigin={{
          horizontal: "right",
          vertical: "bottom",
        }}
        slotProps={{
          paper: {
            sx: {
              width: {
                xs: "95vw",
                sm: 350,
              },

              maxWidth: "100%",

              maxHeight: {
                xs: "80vh",
                sm: 500,
              },

              borderRadius: 2,
              overflow: "hidden",
            },
          },
        }}
      >
        <Box
          sx={{
            p: 2,
            borderBottom: 1,
            borderColor: "divider",
          }}
        >
          <Box
            sx={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
            }}
          >
            <Typography variant="h6">Notifications</Typography>

            <Box>
              <Tooltip title="Mark all as read">
                <IconButton size="small" onClick={handleMarkAllAsRead}>
                  <DoneAll fontSize="small" />
                </IconButton>
              </Tooltip>

              <Tooltip title="Delete all">
                <IconButton size="small" onClick={handleDeleteAll}>
                  <DeleteSweep fontSize="small" />
                </IconButton>
              </Tooltip>
            </Box>
          </Box>
        </Box>

        {loading && notifications.length === 0 ? (
          <Box
            sx={{
              display: "flex",
              justifyContent: "center",
              p: 3,
            }}
          >
            <CircularProgress size={30} />
          </Box>
        ) : notifications.length === 0 ? (
          <Box sx={{ p: 3, textAlign: "center" }}>
            <Typography color="text.secondary">No notifications</Typography>
          </Box>
        ) : (
          <>
            <List
              sx={{
                p: 0,
                overflowY: "auto",

                maxHeight: {
                  xs: "65vh",
                  sm: 380,
                },
              }}
            >
              {notifications.map((notification, index) => (
                <React.Fragment key={notification.id}>
                  <ListItem
                    onClick={() => handleMarkAsRead(notification.id)}
                    sx={{
                      alignItems: "flex-start",
                      gap: 1.5,
                      px: 2,
                      py: 1.5,
                      cursor: "pointer",
                      transition: "0.2s",

                      bgcolor: notification.read_at
                        ? "transparent"
                        : "action.hover",

                      "&:hover": {
                        bgcolor: "action.selected",
                      },
                    }}
                  >
                    <Box
                      sx={{
                        position: "relative",
                        width: 28,
                        minWidth: 28,
                        display: "flex",
                        justifyContent: "center",
                        alignItems: "center",
                        mt: 0.5,
                      }}
                    >
                      {!notification.read_at && (
                        <Circle
                          sx={{
                            position: "absolute",
                            top: -2,
                            right: -2,
                            fontSize: 10,
                            color: "primary.main",
                          }}
                        />
                      )}

                      <NotificationsIcon fontSize="small" />
                    </Box>

                    <Box sx={{ flex: 1, minWidth: 0 }}>
                      <Typography
                        variant="body2"
                        sx={{
                          fontWeight: 400,
                          lineHeight: 1.5,
                          wordBreak: "break-word",
                        }}
                      >
                        {notification.data?.message}
                      </Typography>

                      <Typography
                        variant="caption"
                        color="text.secondary"
                        sx={{
                          display: "block",
                          mt: 0.5,
                        }}
                      >
                        {formatTime(notification.created_at)}
                      </Typography>
                    </Box>
                  </ListItem>

                  {index < notifications.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>

            {hasMore && (
              <Box sx={{ p: 1, textAlign: "center" }}>
                <Button
                  size="small"
                  onClick={() => loadNotifications()}
                  disabled={loading}
                >
                  {loading ? <CircularProgress size={20} /> : "Load more"}
                </Button>
              </Box>
            )}
          </>
        )}
      </Menu>
    </>
  );
};

export default NotificationBell;
