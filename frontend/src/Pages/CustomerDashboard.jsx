import { Box, Card, CardContent, Grid, Typography } from "@mui/material";
import InventoryIcon from "@mui/icons-material/Inventory";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import ClockIcon from "@mui/icons-material/AccessTime";
import CurrencyDollarIcon from "@mui/icons-material/AttachMoney";
import { useEffect, useState } from "react";
import dashboardService from "../services/DashboardService";
import LoadingSpinner from "../Components/LoadingSpinner";

const CustomerDashboard = () => {
  const [data, setData] = useState({});
  const [loadign, setLoadign] = useState(true);
  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await dashboardService.getDataDashboard();

        if (res.success) {
          setData(res.data);
        }
      } catch (error) {
        console.log("DASHBOARD ERROR:", error);
      } finally {
        setLoadign(false);
      }
    };

    fetchData();
  }, []);
  if (loadign) {
    return <LoadingSpinner />;
  }
  return (
    <Box sx={{ p: 3 }}>
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <Card sx={{ bgcolor: "#007bff", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <InventoryIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Total Shipments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.total_shipments}
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <Card sx={{ bgcolor: "#28a745", color: "white", height: "100%" }}>
            <CardContent>
              <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                <CheckCircleIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Delivered Shipments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.delivered_shipments}
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
                <ClockIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Ongoing Shipments
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    {data.ongoing_shipments}
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
                <CurrencyDollarIcon sx={{ fontSize: 48 }} />
                <Box>
                  <Typography variant="body2" sx={{ opacity: 0.9 }}>
                    Total Spent
                  </Typography>
                  <Typography variant="h4" component="div" fontWeight="bold">
                    ${data.total_spent}
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

export default CustomerDashboard;
