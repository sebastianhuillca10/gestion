function mostrarSeccion(nombre) {
    const secciones = document.querySelectorAll('.seccion');
    secciones.forEach(s => s.style.display = 'none');
    document.getElementById(nombre).style.display = 'block';
}