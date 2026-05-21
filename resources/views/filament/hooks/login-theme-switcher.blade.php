<style>
    /* ── Fondo del login ─────────────────────────────────────────── */
    .fi-simple-layout {
        background-image: url('/images/login-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        flex-direction: row !important;
        align-items: stretch !important;
    }

    /* Espaciador invisible sobre la sección morada/izquierda (~58%) */
    .fi-simple-layout::before {
        content: '';
        display: block;
        width: 58%;
        flex-shrink: 0;
        min-height: 100dvh;
    }

    /* El contenedor del form ocupa el área azul (42% derecho) */
    .fi-simple-main-ctn {
        flex: 1 1 auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
        min-height: 100dvh !important;
    }

    /* Limitar ancho del card */
    .fi-simple-main {
        max-width: 400px !important;
        width: 100% !important;
        margin: 0 !important;
    }

    /* ── Centrar el widget de tema en la parte superior ─────────── */
    .fi-simple-layout>.login-top-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        padding: 12px;
        z-index: 50;
    }
</style>

<div class="login-top-bar">
    <x-filament-panels::theme-switcher />
</div>