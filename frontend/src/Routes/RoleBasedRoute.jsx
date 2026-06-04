import { useContext } from "react";
import { Navigate, Outlet } from "react-router-dom";
import { AuthContext } from "../context/UserContext";
import LoadingSpinner from "../Components/LoadingSpinner";

const RoleBasedRoute = ({ allowedRoles = [] }) => {
  const { user, loading } = useContext(AuthContext);
  if (loading) {
    return <LoadingSpinner />;
  }
  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (allowedRoles.length === 0) {
    return <Outlet />;
  }

  if (!allowedRoles.includes(user.role)) {
    const redirectPath = `/${user.role}`;
    return <Navigate to={redirectPath} replace />;
  }

  return <Outlet />;
};

export default RoleBasedRoute;
