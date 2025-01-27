function actualizar_amigo(usuario) {
    // Enviar el alias al servidor con la clave 'usuario_Elegido'
    let datos = { usuario_Elegido: usuario };

    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(datos).toString() // Convertir datos a formato URL
    })
    .then(response => response.text()) // Leer la respuesta como texto
    .then(data => {
        console.log(data); // Mostrar la respuesta del servidor en la consola
        document.getElementById('resultado').textContent = data;  // Mostrar la respuesta en el div
    })
    .catch(error => console.error('Error:', error));  // Manejar errores
    
    alert(`Usuario: ${usuario}`); // Confirmar el valor de usuario en el cliente
}