import { useContext, useEffect, useState } from "react";
import {
  Box,
  Button,
  Pagination,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  TextField,
  Typography,
  Stack,
  Grid,
  CardContent,
  Card,
} from "@mui/material";

import LoadingSpinner from "../../Components/LoadingSpinner";
import recipentService from "../../services/recipentService";
import { useNavigate } from "react-router-dom";
import { AuthContext } from "../../context/UserContext";
import { toast } from "react-toastify";

const Recipients = () => {
  const [Recipients, setRecipients] = useState([]);
  const { user } = useContext(AuthContext);

  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const [search, setSearch] = useState("");
  const [appliedSearch, setAppliedSearch] = useState("");

  const [page, setPage] = useState(1);

  const [sortField, setSortField] = useState("created_at");
  const [sortDirection, setSortDirection] = useState("desc");

  const fetchRecipients = async ({
    currentPage = page,
    currentSearch = appliedSearch,
  } = {}) => {
    try {
      setLoading(true);

      const response = await recipentService.getRecipents(
        currentPage,
        currentSearch,
        sortField,
        sortDirection,
      );

      setRecipients(response.data);
      setMeta(response.meta);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRecipients();
  }, [page, sortField, sortDirection, appliedSearch]);

  const handleFilter = () => {
    setAppliedSearch(search);
    setPage(1);
  };

  const handleClearFilters = () => {
    setSearch("");
    setAppliedSearch("");
    setPage(1);
    setSortField("created_at");
    setSortDirection("desc");
  };

  const handleSort = (field) => {
    if (sortField === field) {
      setSortDirection(sortDirection === "asc" ? "desc" : "asc");
    } else {
      setSortField(field);
      setSortDirection("asc");
    }
  };

  const sortArrow = (field) => {
    if (sortField !== field) return "";
    return sortDirection === "asc" ? " ↑" : " ↓";
  };

  if (loading && Recipients.length === 0) {
    return (
      <Box p={4}>
        <Box
          display="flex"
          justifyContent="center"
          alignItems="center"
          minHeight="400px"
        >
          <LoadingSpinner />
        </Box>
      </Box>
    );
  }

  const handleDelete = async (id) => {
    setLoading(true);
    try {
      const res = await recipentService.deleteRecipent(id);

      toast.success(res.message);

      const updatedRecipients = Recipients.filter(
        (recipient) => recipient.id !== id,
      );
      setRecipients(updatedRecipients);

      setMeta((prevMeta) => ({
        ...prevMeta,
        total: prevMeta.total - 1,
      }));

      if (updatedRecipients.length === 0 && page > 1) {
        setPage(page - 1);
      }
    } catch (error) {
      console.error(error);
      toast.error("Failed to delete shipment");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box p={4}>

      <Box mb={4}>
        <Card elevation={2} sx={{ borderRadius: 3 }}>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, sm: 6, md: 10 }}>
                <TextField
                  fullWidth
                  label="Search Recipient"
                  placeholder=" ID / Name / Phone"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  size="small"
                />
              </Grid>

              <Grid size={{ xs: 12, sm: 6, md: 2 }}>
                <Stack
                  direction={{ xs: "column", sm: "row" }}
                  spacing={2}
                  justifyContent="flex-end"
                >
                  <Button
                    variant="contained"
                    size="large"
                    onClick={handleFilter}
                  >
                    Search
                  </Button>

                  <Button
                    variant="outlined"
                    size="large"
                    onClick={handleClearFilters}
                  >
                    Clear
                  </Button>
                </Stack>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      </Box>

      <TableContainer component={Paper} sx={{ mt: 3 }}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell
                align="center"
                onClick={() => handleSort("id")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                ID{sortArrow("id")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("receiver_name")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Receiver Name{sortArrow("receiver_name")}
              </TableCell>

              <TableCell
                align="center"
                onClick={() => handleSort("receiver_phone")}
                sx={{ cursor: "pointer", fontWeight: "bold" }}
              >
                Receiver Phone{sortArrow("receiver_phone")}
              </TableCell>

              <TableCell align="center">Actions</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {Recipients.length > 0 ? (
              Recipients.map((Recipient) => (
                <TableRow key={Recipient.id}>
                  <TableCell align="center">{Recipient.id}</TableCell>
                  <TableCell align="center">
                    {Recipient.receiver_name}
                  </TableCell>
                  <TableCell align="center">
                    {Recipient.receiver_phone}
                  </TableCell>
                  <TableCell
                    sx={{ display: "flex", justifyContent: "center", gap: 2 }}
                  >
                    <Button
                      variant="outlined"
                      onClick={() =>
                        navigate(
                          `/${user?.role}/recipients/${Recipient.id}/edit`,
                        )
                      }
                    >
                      Edit
                    </Button>
                    <Button
                      variant="outlined"
                      color="error"
                      onClick={() => handleDelete(Recipient.id)}
                    >
                      delete
                    </Button>
                  </TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={4} align="center">
                  {loading ? <LoadingSpinner /> : "No Receiver found"}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {Recipients.length > 0 && (
        <Box mt={3} sx={{ mt: 3, display: "flex", justifyContent: "center" }}>
          <Pagination
            count={meta.last_page || 1}
            page={page}
            onChange={(e, value) => setPage(value)}
            color="primary"
          />
        </Box>
      )}
    </Box>
  );
};

export default Recipients;
