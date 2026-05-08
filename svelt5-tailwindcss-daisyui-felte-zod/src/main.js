import { mount } from 'svelte'
import './app.css'
import App from './App.svelte'

document.documentElement.setAttribute("data-theme", "emerald");  // Establece el tema al cargar la aplicación

const app = mount(App, {
  target: document.getElementById('app'),
})

export default app
