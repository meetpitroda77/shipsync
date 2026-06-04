import { createContext } from "react";
import useLocalStorage from "../hooks/useLocalStorage";

const FavoriteContext = createContext();

const FavoriteProvider = ({ children }) => {
  const [favorites, setFavorites] = useLocalStorage("favorites", []);

  const addToFavorites = (job) => {
    const exists = favorites.find((item) => item.id === job.id);
    if (!exists) {
      setFavorites([...favorites, job]);
    }
  };

  const removeFavorites = (jobId) => {
    setFavorites(favorites.filter((item) => item.id !== jobId));
  };

  const isFavorite = (id) => {
    return favorites.some((item) => item.id === id);
  };

  return (
    <FavoriteContext.Provider
      value={{ favorites, addToFavorites, removeFavorites, isFavorite }}
    >
      {children}
    </FavoriteContext.Provider>
  );
};

export { FavoriteContext, FavoriteProvider };
