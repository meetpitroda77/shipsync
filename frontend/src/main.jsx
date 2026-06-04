import { createRoot } from "react-dom/client";
import "./index.css";
import App from "./App.jsx";
import { ToastContainer } from "react-toastify";
import { AuthProvider } from "./context/UserContext.jsx";
import { SubscriptionProvider } from "./context/SubscriptionContext.jsx";
createRoot(document.getElementById("root")).render(
  <AuthProvider>
    <SubscriptionProvider>
      <App />
      <ToastContainer />
    </SubscriptionProvider>
  </AuthProvider>,
);
  