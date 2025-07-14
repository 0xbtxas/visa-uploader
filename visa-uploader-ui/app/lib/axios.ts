import axios from "axios";
import { API_BASE_URL } from "./config";

export const api = axios.create({
  baseURL: `${API_BASE_URL}/api`,
  headers: {
    Accept: "application/json",
  },
});

// Optional: Add interceptors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Global error logging
    console.error("API Error:", error);
    return Promise.reject(error);
  }
);
