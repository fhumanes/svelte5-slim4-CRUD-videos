import Toasts from "../notification/Toasts.svelte";
import { addToast } from "../notification/store";

// import { auth, group, logout } from '../stores/auth.js';

export function handleFatalError({ err , cambiarVista }) {
   
    const status = err.response?.status;
    const message = err.response?.data?.message;
    const message2 = err.response?.message
    // console.log('detalles del error: ', status, message, message2);         
    if ( status === 400) { // Error controlado y muestra mensaje y sigue la APP
            addToast({
                message: message,
                type: 'error',
                dismissible: true,
                timeout: 6000
            });
            return; 
        }
    console.error('al inciar rutina de error: ', err);

    if ( status !== 500) {
            addToast({
                message: message,
                type: 'error',
                dismissible: true,
                timeout: 6000
            });
        } else {
            addToast({
                message: "Error grave del servidor -500-. Por favor, inténtelo más tarde."+message2,
                type: 'error',
                dismissible: true,
                timeout: 0
            });
        }       

    console.warn("Error Fatal - Status: " + status +' - '+ (message2 ? message2 : message));

    // logout();
    // cambiarVista('login');
}
