import { Box, Card, CardContent, Grid, Typography } from "@mui/material";
import InventoryIcon from "@mui/icons-material/Inventory";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import PendingIcon from "@mui/icons-material/Pending";
import { useEffect, useState } from "react";
import dashboardService from "../services/DashboardService";
import LoadingSpinner from "../Components/LoadingSpinner";

const AgentDashboard = () => {
  const [data, setData] = useState({
    assigned_shipments: 0,
    delivered_today: 0,
    pending_delivery: 0,
  });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      const res = await dashboardService.getDataDashboard();
      if (res.success) {
        setData(res.data);
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <Box sx={{ p: 3 }}>
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, sm: 6, lg: 4 }}>
          <Card sx={{ bgcolor: "#007bff", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <InventoryIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Assigned Shipments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.assigned_shipments}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, sm: 6, lg: 4 }}>
          <Card sx={{ bgcolor: "#28a745", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <CheckCircleIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Delivered Today
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.delivered_today}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, sm: 6, lg: 4 }}>
          <Card sx={{ bgcolor: "#ffc107", color: "black", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <PendingIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Pending Delivery
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.pending_delivery}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
};

export default AgentDashboard;
