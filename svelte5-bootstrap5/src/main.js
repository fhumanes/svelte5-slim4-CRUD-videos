import { mount } from 'svelte'
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import * as bootstrap from 'bootstrap';
import './app.css'
import App from './App.svelte';

// Hacer bootstrap disponible globalmente (necesario para el navbar)
window.bootstrap = bootstrap;

const app = mount(App, {
  target: document.getElementById('app'),
})

export default app
