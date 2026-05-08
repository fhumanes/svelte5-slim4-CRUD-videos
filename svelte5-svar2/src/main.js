    // main.js
    import { mount } from 'svelte'

    // Importa el componente que quieras probar
    // import App from './components/MovieGrid.svelte' // Deja este para películas
    // import App from './components/ThemeGrid.svelte' // Descomenta para probar temas
    // import App from './components/SupportGrid.svelte' // Descomenta para probar soportes
    import App from './App.svelte'

    const app = mount(App, {
      target: document.getElementById('app'),
    })

    export default app
    