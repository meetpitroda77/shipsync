import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { toast } from "react-toastify";
import authService from "../../services/authService";
import LoadingSpinner from "../../Components/LoadingSpinner";

const VerifyEmail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const verifyUserEmail = async () => {
      if (!id) {
        toast.error("Invalid verification link");
        setLoading(false);
        setTimeout(() => navigate("/login"), 2000);
        return;
      }

      try {
        const response = await authService.verifyEmail(id);

        if (response.success) {
          toast.success(response.message || "Email verified successfully!");
        } else {
          toast.error(response.message || "Verification failed");
        }
      } catch (error) {
        const errorMessage =
          error.response?.data?.message ||
          "Verification failed. Please try again.";
        toast.error(errorMessage);
      } finally {
        setLoading(false);
        navigate("/login");
      }
    };

    verifyUserEmail();
  }, [id, navigate]);

  if (loading) return <LoadingSpinner />;

  return null;
};

export default VerifyEmail;
