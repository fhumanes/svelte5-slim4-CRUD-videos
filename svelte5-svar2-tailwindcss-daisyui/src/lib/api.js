import axios from 'axios';

let appHost = window.location.hostname;

let API_BASE_URL = 'http://localhost/movie-php-app/public';

if (appHost !== 'localhost') {
  API_BASE_URL = 'https://fhumanes.com/movie-php-app/public';
}

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;
