import './bootstrap';

// 1. Importar la librería de iconos y el paquete completo de iconos
import { createIcons, icons } from 'lucide';

// 2. Inicializar los iconos al cargar la página
createIcons({
  icons
});

// Opcional: Si usas navegación dinámica o Livewire, a veces es necesario
// re-inicializar cuando el DOM cambia. Puedes agregar esto por seguridad:
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});