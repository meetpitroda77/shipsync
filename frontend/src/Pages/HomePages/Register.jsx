import { useState } from "react";
import { Link } from "react-router-dom";
import { toast } from "react-toastify";
import authService from "../../services/authService";

const Register = () => {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    password: "",
    city: "",
    state: "",
    country: "",
    zip_code: "",
    address: "",
  });

  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();

    setErrors({});

    try {
      const res = await authService.register(formData);
      if (res.success) {
        toast.success(res.message);
        setFormData({
          name: "",
          email: "",
          phone: "",
          password: "",
          city: "",
          state: "",
          country: "",
          zip_code: "",
          address: "",
        });
        return;
      }
    } catch (error) {
      if (error.response && error.response.data) {
        const responseData = error.response.data;

        if (responseData.errors) {
          setErrors(responseData.errors);
          return;
        }
      }
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 py-10 px-4">
      <div className="max-w-5xl mx-auto">
        <div className="bg-white shadow-lg rounded-xl overflow-hidden">
          <div className="border-b px-6 py-5">
            <h1 className="text-3xl font-bold text-center text-blue-600">
              Register
            </h1>
            <p className="text-center text-gray-500 mt-2">
              Create a new account
            </p>
          </div>

          <div className="p-4 md:p-6">
            <form onSubmit={handleSubmit}>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Full Name
                  </label>

                  <input
                    type="text"
                    value={formData.name}
                    placeholder="Enter full name"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.name
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        name: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        name: null,
                      });
                    }}
                  />

                  {errors.name && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.name[0]}
                    </small>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Email
                  </label>

                  <input
                    type="text"
                    value={formData.email}
                    placeholder="Enter email"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.email
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        email: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        email: null,
                      });
                    }}
                  />

                  {errors.email && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.email[0]}
                    </small>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Phone
                  </label>

                  <input
                    type="text"
                    value={formData.phone}
                    placeholder="Enter phone number"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.phone
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        phone: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        phone: null,
                      });
                    }}
                  />

                  {errors.phone && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.phone[0]}
                    </small>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Password
                  </label>

                  <input
                    type="password"
                    value={formData.password}
                    placeholder="Enter password"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.password
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        password: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        password: null,
                      });
                    }}
                  />

                  {errors.password && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.password[0]}
                    </small>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    City
                  </label>

                  <input
                    type="text"
                    value={formData.city}
                    placeholder="Enter city"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.city
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        city: e.target.value,
                      });
                      setErrors({
                        ...errors,
                        city: null,
                      });
                    }}
                  />

                  {errors.city && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.city[0]}
                    </small>
                  )}
                </div>

                <div>
                  {" "}
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    State
                  </label>
                  <input
                    type="text"
                    value={formData.state}
                    placeholder="Enter state"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.state
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        state: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        state: null,
                      });
                    }}
                  />
                  {errors.state && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.state[0]}
                    </small>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Country
                  </label>

                  <input
                    type="text"
                    value={formData.country}
                    placeholder="Enter country"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.country
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        country: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        country: null,
                      });
                    }}
                  />

                  {errors.country && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.country[0]}
                    </small>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Zip Code
                  </label>

                  <input
                    type="text"
                    value={formData.zip_code}
                    placeholder="Enter zip code"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.zip_code
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        zip_code: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        zip_code: null,
                      });
                    }}
                  />

                  {errors.zip_code && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.zip_code[0]}
                    </small>
                  )}
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Address
                  </label>

                  <textarea
                    rows="3"
                    value={formData.address}
                    placeholder="Enter address"
                    className={`w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition ${
                      errors.address
                        ? "border-red-500 focus:ring-red-300"
                        : "border-gray-300 focus:ring-blue-400"
                    }`}
                    onChange={(e) => {
                      setFormData({
                        ...formData,
                        address: e.target.value,
                      });

                      setErrors({
                        ...errors,
                        address: null,
                      });
                    }}
                  />

                  {errors.address && (
                    <small className="text-red-600 text-xs mt-1 block">
                      {errors.address[0]}
                    </small>
                  )}
                </div>
              </div>

              {/* Button */}
              <div className="mt-8">
                <button
                  type="submit"
                  className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition duration-200"
                >
                  Sign Up
                </button>
              </div>

              {/* Login */}
              <p className="text-center text-sm text-gray-600 mt-5">
                Already have an account?{" "}
                <Link
                  to="/login"
                  className="text-blue-600 hover:underline font-medium"
                >
                  Login
                </Link>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Register;
