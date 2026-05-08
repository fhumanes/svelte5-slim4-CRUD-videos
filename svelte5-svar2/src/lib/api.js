// src/lib/api.js (o src/api/index.js)
import axios from 'axios';

let appHost = '';
appHost = window.location.hostname; // Para obtener el host de la URL
console.log("estoy conectado al Host: ",appHost);

// URL base de tu API REST de PHP
// Asegúrate de que esta URL sea la correcta para tu PUBLIC folder
let API_BASE_URL = 'http://localhost/movie-server/v1';

if (appHost == 'localhost') {
    API_BASE_URL = 'http://localhost/movie-server/v1';
} else {
    API_BASE_URL = 'https://fhumanes.com/movie-server/v1';
}

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

export default api;