import { Box, Card, CardContent, Grid, Typography } from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import WarningIcon from "@mui/icons-material/Warning";
import PendingActionsIcon from "@mui/icons-material/PendingActions";
import ErrorIcon from "@mui/icons-material/Error";
import { useEffect, useState } from "react";
import dashboardService from "../services/DashboardService";
import LoadingSpinner from "../Components/LoadingSpinner";

const StaffDashboard = () => {
  const [data, setData] = useState({
    delivered_today: 0,
    delayed_shipments: 0,
    pending_assignments: 0,
    failed_deliveries: 0
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
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
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

        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <Card sx={{ bgcolor: "#ffc107", color: "black", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <WarningIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Delayed Shipments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.delayed_shipments}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <Card sx={{ bgcolor: "#17a2b8", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <PendingActionsIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Pending Assignments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.pending_assignments}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <Card sx={{ bgcolor: "#dc3545", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <ErrorIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Failed Deliveries
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.failed_deliveries}
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

export default StaffDashboard;