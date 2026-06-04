import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: "pusher",

  key: import.meta.env.VITE_PUSHER_APP_KEY,

  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,

  forceTLS: true,

  enabledTransports: ["ws", "wss"],

  authEndpoint: "http://localhost:8000/broadcasting/auth",

  withCredentials: true,

  auth: {
    headers: {
      Accept: "application/json",

      Authorization: `Bearer ${JSON.parse(localStorage.getItem("token"))}`,
    },
  },
});

export default echo;
