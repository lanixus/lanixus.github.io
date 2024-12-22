document.getElementById('toggleButton').addEventListener('click', function() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar.style.left === '0px') {
        sidebar.style.left = '-250px';
        this.textContent = 'Abrir Menú';
    } else {
        sidebar.style.left = '0px';
        this.textContent = 'Cerrar Menú';
    }
});
