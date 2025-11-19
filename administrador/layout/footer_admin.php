<footer class="footer-global">
  <div class="footer-contenido">
    <p>&copy; <?php echo date("Y"); ?> CECYTEM | Sistema de Asistencia</p>
    <p class="mini">Desarrollado por grupo Dual 2023-2026</p>
  </div>
</footer>

<!-- Estilos del footer -->
<style>
/* Hace que el footer se mantenga siempre abajo */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
}

/* El contenido principal debe ocupar el espacio disponible */
main {
    flex: 1;
}

/* FOOTER */
.footer-global {
    width: 100%;
    text-align: center;
    padding: 15px 0;
    background: rgba(0,0,0,0.3);
    color: white;
    font-family: 'Segoe UI', sans-serif;
    backdrop-filter: blur(5px);
    margin-top: auto;   /* 👍 Esto lo empuja hacia abajo */
}

.footer-global .mini {
    font-size: 0.8em;
    opacity: 0.8;
}

/* Modo oscuro */
.dark-mode .footer-global {
    background: rgba(255,255,255,0.1);
    color: #e2e2e2;
}
</style>
