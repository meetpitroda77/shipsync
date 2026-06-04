import { useContext, useState } from "react";
import { Outlet, useNavigate } from "react-router-dom";
import { Link } from "react-router-dom";
import logo from "../assets/shipcync.png";
import LoadingSpinner from "../Components/LoadingSpinner";
import { AuthContext } from "../context/UserContext";
import { toast } from "react-toastify";

const MainLayout = () => {
  const [isOpen, setIsOpen] = useState(false);
  const navigate = useNavigate();

  const { user, logout, loading: authLoading } = useContext(AuthContext);
  const [logoutLoading, setLogoutLoading] = useState(false);

  const handleLogout = async () => {
    try {
      setLogoutLoading(true);
      await logout();
      navigate("/login", { replace: true });
      toast.success("Logged out successfully");
    } catch (error) {
      console.error("Logout failed:", error);
    } finally {
      setLogoutLoading(false);
    }
  };

  if (authLoading || logoutLoading) return <LoadingSpinner />;

  return (
    <>
      <nav className="bg-white border-b border-gray-200">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center h-16">
            <Link to="/" className="flex items-center">
              <img src={logo} alt="Logo" width="150" className="brand-image" />
            </Link>

            <button
              onClick={() => setIsOpen(!isOpen)}
              className="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none"
            >
              <svg
                className="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                {isOpen ? (
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M6 18L18 6M6 6l12 12"
                  />
                ) : (
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                )}
              </svg>
            </button>

            <div className="hidden lg:flex lg:items-center lg:justify-between flex-1 ml-8">
              <ul className="flex space-x-8 mx-auto">
                <li>
                  <Link
                    to="/"
                    className="text-gray-700 hover:text-gray-900 font-medium transition-colors"
                  >
                    Home
                  </Link>
                </li>
                <li>
                  <Link
                    to="/track-shipment"
                    className="text-gray-700 hover:text-gray-900 font-medium transition-colors"
                  >
                    Tracking
                  </Link>
                </li>
              </ul>

              {/* Show different buttons based on auth status */}
              <div className="flex gap-3">
                {user ? (
                  <>
                    <span className="px-3 py-2 text-gray-700 font-medium">
                      Welcome, {user.name || user.email}
                    </span>
                    <button
                      onClick={handleLogout}
                      className="px-5 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition-colors font-medium"
                    >
                      Logout
                    </button>
                  </>
                ) : (
                  <>
                    <Link
                      to="/login"
                      className="px-5 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition-colors font-medium"
                    >
                      Sign In
                    </Link>
                    <Link
                      to="/register"
                      className="px-5 py-2 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-colors font-medium"
                    >
                      Get Started
                    </Link>
                  </>
                )}
              </div>
            </div>

            {/* Mobile Navigation */}
            {isOpen && (
              <div className="lg:hidden absolute top-16 left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-50">
                <div className="px-4 py-3 space-y-3">
                  <Link
                    to="/"
                    className="block py-2 text-gray-700 hover:text-gray-900 font-medium"
                    onClick={() => setIsOpen(false)}
                  >
                    Home
                  </Link>
                  <Link
                    to="/track-shipment"
                    className="block py-2 text-gray-700 hover:text-gray-900 font-medium"
                    onClick={() => setIsOpen(false)}
                  >
                    Tracking
                  </Link>
                  <div className="pt-3 flex flex-col gap-3">
                    {user ? (
                      <>
                        <span className="px-3 py-2 text-gray-700 font-medium">
                          Welcome, {user.name || user.email}
                        </span>
                        <button
                          onClick={() => {
                            handleLogout();
                            setIsOpen(false);
                          }}
                          className="text-center px-5 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition-colors font-medium"
                        >
                          Logout
                        </button>
                      </>
                    ) : (
                      <>
                        <Link
                          to="/login"
                          className="text-center px-5 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition-colors font-medium"
                          onClick={() => setIsOpen(false)}
                        >
                          Sign In
                        </Link>
                        <Link
                          to="/register"
                          className="text-center px-5 py-2 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-colors font-medium"
                          onClick={() => setIsOpen(false)}
                        >
                          Get Started
                        </Link>
                      </>
                    )}
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </nav>
      <main>
        <Outlet />
      </main>
    </>
  );
};

export default MainLayout;
