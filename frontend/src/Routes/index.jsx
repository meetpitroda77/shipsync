import {
  createBrowserRouter,
  createRoutesFromElements,
  Navigate,
  Route,
  RouterProvider,
} from "react-router-dom";
import MainLayout from "../Layouts/MainLayout";

import Home from "../Pages/HomePages/Home";

import Login from "../Pages/HomePages/Login";
import Register from "../Pages/HomePages/Register";
import TrackShipment from "../Pages/HomePages/TrackShipment";
import VerifyEmail from "../Pages/HomePages/VerifyEmail";
import CustomerLayout from "../Layouts/CustomerLayout";
import CreateShipment from "../Pages/Shipment/CreateShipment";
import CustomerDashboard from "../Pages/CustomerDashboard";
import Shipments from "../Pages/Shipment/Shipments";
import ShipmentDetails from "../Pages/Shipment/ShipmentDetails";
import Recipients from "../Pages/Recipient/Recipients";
import CreateRecipient from "../Pages/Recipient/CreateRecipient";
import EditRecipient from "../Pages/Recipient/EditRecipient";
import ProfileEdit from "../Pages/HomePages/ProfileEdit";
import PaymentStatus from "../Pages/Shipment/PaymentStatus";
import { AdminDashboard } from "../Pages/AdminDashboard";
import AdminLayout from "../Layouts/AdminLayout";
import Payments from "../Pages/Payments";
import AgentLayout from "../Layouts/AgentLayout";
import AgentDashboard from "../Pages/AgentDashboard";
import StaffLAyout from "../Layouts/StaffLayout";
import StaffDashboard from "../Pages/StaffDashboard";
import Report from "../Pages/report/Report";
import Users from "../Pages/users/Users";
import RoleBasedRoute from "./RoleBasedRoute";
import { CreateUser } from "../Pages/users/CreateUser";
import Settings from "../Pages/Settings";
import DailyReport from "../Pages/report/DailyReport";
import ForgotPassword from "../Pages/HomePages/ForgotPassword";
import ResetPassword from "../Pages/HomePages/ResetPassword";
import SubscriptionPlans from "../Pages/Subscription/SubscriptionPlans";
import SubscriptionDashboard from "../Pages/Subscription/SubscriptionDashboard";
import SubscriptionPaymentStatus from "../Pages/Subscription/SubscriptionPaymentStatus";
import CustomerShipmentGuard from "../Components/CustomerShipmentGuard";

const router = createBrowserRouter(
  createRoutesFromElements(
    <Route>
      <Route element={<MainLayout />}>
        <Route index element={<Home />} />
        <Route path="login" element={<Login />} />
        <Route path="register" element={<Register />} />
        <Route path="verify-email/:id" element={<VerifyEmail />} />

        <Route path="forgot-password" element={<ForgotPassword />} />
        <Route path="reset-password" element={<ResetPassword />} />
        <Route path="track-shipment" element={<TrackShipment />} />
      </Route>

      <Route path="/payment/success" element={<PaymentStatus />} />
      <Route path="/payment/cancel" element={<PaymentStatus />} />
      <Route
        path="/subscription/success"
        element={<SubscriptionPaymentStatus />}
      />
      <Route
        path="/subscription/cancel"
        element={<SubscriptionPaymentStatus />}
      />

      <Route
        path="/admin"
        element={<RoleBasedRoute allowedRoles={["admin"]} />}
      >
        <Route element={<AdminLayout />}>
          <Route index element={<AdminDashboard />} />
          <Route path="shipments">
            <Route index element={<Shipments />} />
            <Route path="create" element={<CreateShipment />} />
            <Route path=":id" element={<ShipmentDetails />} />
            <Route path=":id/edit" element={<CreateShipment />} />
          </Route>
          <Route path="payments" element={<Payments />} />
          <Route path="reports" element={<Report />} />
          <Route path="daily-report" element={<DailyReport />} />
          <Route path="recipients">
            <Route index element={<Recipients />} />
            <Route path="create" element={<CreateRecipient />} />
            <Route path=":id/edit" element={<EditRecipient />} />
          </Route>
          <Route path="profile/edit" element={<ProfileEdit />} />
          <Route path="settings" element={<Settings />} />

          <Route path="users">
            <Route index element={<Users />} />
            <Route path="create" element={<CreateUser />} />
          </Route>
        </Route>
      </Route>

      <Route
        path="/customer"
        element={<RoleBasedRoute allowedRoles={["customer"]} />}
      >
        <Route element={<CustomerLayout />}>
          <Route index element={<CustomerDashboard />} />
          <Route path="shipments">
            <Route index element={<Shipments />} />
            <Route element={<CustomerShipmentGuard />}>
              <Route path="create" element={<CreateShipment />} />
            </Route>{" "}
            <Route path="track-shipment" element={<TrackShipment />} />
            <Route path=":id" element={<ShipmentDetails />} />
          </Route>
          <Route path="payments" element={<Payments />} />
          <Route path="recipients">
            <Route index element={<Recipients />} />
            <Route path="create" element={<CreateRecipient />} />
            <Route path=":id/edit" element={<EditRecipient />} />
          </Route>
          <Route path="profile/edit" element={<ProfileEdit />} />
          <Route path="subscription/plans" element={<SubscriptionPlans />} />

          <Route
            path="subscription/dashboard"
            element={<SubscriptionDashboard />}
          />
        </Route>
      </Route>

      <Route
        path="/agent"
        element={<RoleBasedRoute allowedRoles={["agent"]} />}
      >
        <Route element={<AgentLayout />}>
          <Route index element={<AgentDashboard />} />
          <Route path="shipments">
            <Route index element={<Shipments />} />
            <Route path=":id" element={<ShipmentDetails />} />
          </Route>
          <Route path="payments" element={<Payments />} />
          <Route path="profile/edit" element={<ProfileEdit />} />
        </Route>
      </Route>

      <Route
        path="/staff"
        element={<RoleBasedRoute allowedRoles={["staff"]} />}
      >
        <Route element={<StaffLAyout />}>
          <Route index element={<StaffDashboard />} />
          <Route path="shipments">
            <Route index element={<Shipments />} />
            <Route path=":id" element={<ShipmentDetails />} />
          </Route>
          <Route path="payments" element={<Payments />} />
          <Route path="profile/edit" element={<ProfileEdit />} />
        </Route>
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Route>,
  ),
);
export const AppRouter = () => {
  return <RouterProvider router={router} />;
};
