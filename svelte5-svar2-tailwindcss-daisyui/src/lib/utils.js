// src/lib/functions.js

// Funciones de utilidad para el manejo de fechas, si las necesitas en otros lugares
export function toValidAPIDate(input) {
  const date = (input instanceof Date) ? input : new Date(input);
  return formatDateToAPI(date);
}

export function formatDateToAPI(dateObj) {
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, "0");
  const day = String(dateObj.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

/**
 * Convierte una cadena de fecha de MySQL (yyyy-mm-dd) a un objeto Date
 * sin el desplazamiento horario de la zona local.
 * * @param {string} mysqlDate La cadena de fecha en formato 'yyyy-mm-dd'.
 * @returns {Date} Un objeto Date que representa la fecha.
 */
export function createDateFromMySQL(mysqlDate) {
  // Separa la cadena en año, mes y día
  const dateParts = mysqlDate.split('-');
  
  // Convierte las partes a números enteros
  // El mes en JavaScript es base 0 (0-11), por lo que restamos 1
  const year = parseInt(dateParts[0]);
  const month = parseInt(dateParts[1]) - 1; 
  const day = parseInt(dateParts[2]);
  
  // Crea el objeto Date en la zona horaria local, sin la hora.
  return new Date(year, month, day);
}

/**
 * Convierte un objeto Date en una cadena de texto con formato dd-mm-yyyy.
 * @param {Date} dateObject El objeto Date a formatear.
 * @returns {string} La fecha en formato dd-mm-yyyy.
 */
export function formatDateToDDMMYYYY(dateObject) {
  // Obtenemos el día y le añadimos un cero si es menor de 10
  const day = String(dateObject.getDate()).padStart(2, '0');
  
  // Obtenemos el mes (los meses en JS son base 0, así que sumamos 1)
  // y le añadimos un cero si es menor de 10
  const month = String(dateObject.getMonth() + 1).padStart(2, '0');
  
  // Obtenemos el año
  const year = dateObject.getFullYear();
  
  // Devolvemos la cadena formateada
  return `${day}/${month}/${year}`;
}
