import { createContext, useEffect, useState } from "react";
import authService from "../services/authService";
import useLocalStorage from "../hooks/useLocalStorage";

const AuthContext = createContext();

const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [token, setToken] = useLocalStorage("token", null);

  const fetchUser = async () => {
    try {
      const res = await authService.getUser();

      setUser(res.user);

      return res.user;
    } catch (error) {
      setUser(null);
      throw error;
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      setUser(null);
      setToken(null);
      localStorage.removeItem("token");
    }
  };
  useEffect(() => {
    const initAuth = async () => {
      setLoading(true);

      if (token) {
        if (!user) {
          try {
            await fetchUser();
          } catch (error) {
            setUser(null);
            setToken(null);
          }
        }
      } else {
        setUser(null);
      }

      setLoading(false);
    };

    initAuth();
  }, [token]);

  return (
    <AuthContext.Provider
      value={{
        user,
        setUser,
        logout,
        loading,
        setLoading,
        fetchUser,
        token,
        setToken,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
export { AuthProvider, AuthContext };
