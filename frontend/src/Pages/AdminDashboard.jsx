import { Box, Card, CardContent, Grid, Typography, Paper } from "@mui/material";
import InventoryIcon from "@mui/icons-material/Inventory";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import PendingIcon from "@mui/icons-material/Pending";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import PeopleIcon from "@mui/icons-material/People";
import TrendingUpIcon from "@mui/icons-material/TrendingUp";
import ScheduleIcon from "@mui/icons-material/Schedule";
import { useEffect, useState, useRef } from "react";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend,
} from "chart.js";
import { Chart } from "chart.js/auto";

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);
import dashboardService from "../services/DashboardService";
import LoadingSpinner from "../Components/LoadingSpinner";

export const AdminDashboard = () => {
  const [data, setData] = useState({
    chart: {
      months: [],
      shipments: [],
      revenue: [],
    },
    stats: {
      total_shipments: 0,
      total_revenue: 0,
      total_users: 0,
      completed_shipments: 0,
      delivery_success_rate: 0,
      avg_delivery_time_days: 0,
      ongoing_shipments: 0,
    },
  });
  const [loading, setLoading] = useState(false);
  const shipmentsChartRef = useRef(null);
  const revenueChartRef = useRef(null);

  const shipmentsChartInstance = useRef(null);
  const revenueChartInstance = useRef(null);
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
  useEffect(() => {
    if (!loading && data.chart.months.length > 0) {
      if (shipmentsChartInstance.current) {
        shipmentsChartInstance.current.destroy();
      }

      if (revenueChartInstance.current) {
        revenueChartInstance.current.destroy();
      }

      shipmentsChartInstance.current = new Chart(shipmentsChartRef.current, {
        type: "line",
        data: {
          labels: data.chart.months,
          datasets: [
            {
              label: "Total Shipments",
              data: data.chart.shipments,
              borderColor: "#0d6efd",
              backgroundColor: "rgba(13,110,253,0.15)",
              fill: true,
              tension: 0.4,
              pointRadius: 5,
              pointHoverRadius: 7,
              borderWidth: 3,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: {
              position: "top",
            },
          },

          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Shipments",
              },
            },
          },
        },
      });

      revenueChartInstance.current = new Chart(revenueChartRef.current, {
        type: "bar",
        data: {
          labels: data.chart.months,
          datasets: [
            {
              label: "Total Revenue",
              data: data.chart.revenue,
              backgroundColor: "#20c997",
              borderRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: {
              position: "top",
            },

            tooltip: {
              callbacks: {
                label(context) {
                  return `Revenue: $${Number(context.raw).toLocaleString()}`;
                },
              },
            },
          },

          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Revenue ($)",
              },
              ticks: {
                callback(value) {
                  return "$" + Number(value).toLocaleString();
                },
              },
            },
          },
        },
      });
    }

    return () => {
      if (shipmentsChartInstance.current) {
        shipmentsChartInstance.current.destroy();
      }

      if (revenueChartInstance.current) {
        revenueChartInstance.current.destroy();
      }
    };
  }, [loading, data.chart]);
  if (loading) {
    return <LoadingSpinner />;
  }

  const statsCards = [
    {
      title: "Total Shipments",
      value: data.stats.total_shipments,
      icon: <InventoryIcon sx={{ fontSize: 48 }} />,
      color: "#007bff",
    },
    {
      title: "Total Revenue",
      value: `$${data.stats.total_revenue.toLocaleString()}`,
      icon: <AttachMoneyIcon sx={{ fontSize: 48 }} />,
      color: "#28a745",
    },
    {
      title: "Total Users",
      value: data.stats.total_users,
      icon: <PeopleIcon sx={{ fontSize: 48 }} />,
      color: "#17a2b8",
    },
    {
      title: "Completed Shipments",
      value: data.stats.completed_shipments,
      icon: <CheckCircleIcon sx={{ fontSize: 48 }} />,
      color: "#28a745",
    },
    {
      title: "Delivery Success Rate",
      value: `${data.stats.delivery_success_rate}%`,
      icon: <TrendingUpIcon sx={{ fontSize: 48 }} />,
      color: "#6f42c1",
    },
    {
      title: "Avg Delivery Time",
      value: `${data.stats.avg_delivery_time_days} days`,
      icon: <ScheduleIcon sx={{ fontSize: 48 }} />,
      color: "#fd7e14",
    },
    {
      title: "Ongoing Shipments",
      value: data.stats.ongoing_shipments,
      icon: <PendingIcon sx={{ fontSize: 48 }} />,
      color: "#ffc107",
    },
  ];

  return (
    <Box sx={{ p: 3 }}>
      <Grid container spacing={3} sx={{ mb: 4 }}>
        {statsCards.map((card, index) => (
          <Grid size={{ xs: 12, sm: 6, md: 4, lg: 3 }} key={index}>
            <Card sx={{ bgcolor: card.color, color: "white", height: "100%" }}>
              <CardContent>
                <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                  {card.icon}
                  <Box>
                    <Typography variant="body2" sx={{ opacity: 0.9 }}>
                      {card.title}
                    </Typography>
                    <Typography variant="h5" component="div" fontWeight="bold">
                      {card.value}
                    </Typography>
                  </Box>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Monthly Shipments 
            </Typography>

            <Box sx={{ height: 350 }}>
              <canvas ref={shipmentsChartRef}></canvas>
            </Box>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Monthly Revenue 
            </Typography>

            <Box sx={{ height: 350 }}>
              <canvas ref={revenueChartRef}></canvas>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
};

export default AdminDashboard;
