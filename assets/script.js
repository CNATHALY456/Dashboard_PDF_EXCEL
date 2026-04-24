window.onload = async function() {

    const ciudades = [
        { nombre: "San Salvador", lat: 13.69, lon: -89.19 },
        { nombre: "Santa Ana", lat: 13.99, lon: -89.56 },
        { nombre: "San Miguel", lat: 13.48, lon: -88.18 },
        { nombre: "La Unión", lat: 13.34, lon: -87.84 },
        { nombre: "Sonsonate", lat: 13.72, lon: -89.72 }
    ];

    let nombres = [];
    let temperatura = [];
    let humedad = [];
    let lluvia = [];

    for (let ciudad of ciudades) {

        let url = `https://api.open-meteo.com/v1/forecast?latitude=${ciudad.lat}&longitude=${ciudad.lon}&current=temperature_2m,relative_humidity_2m,precipitation`;

        let res = await fetch(url);
        let data = await res.json();

        nombres.push(ciudad.nombre);
        temperatura.push(data.current.temperature_2m);
        humedad.push(data.current.relative_humidity_2m);

        let lluviaValor = data.current.precipitation;
        lluvia.push(lluviaValor > 0 ? lluviaValor : 1);
    }

    /* TABLA */
    const tabla = document.getElementById("tablaDatos");

    for (let i = 0; i < nombres.length; i++) {
        tabla.innerHTML += `
            <tr>
                <td>${nombres[i]}</td>
                <td>${lluvia[i]}</td>
                <td>${temperatura[i]}</td>
                <td>${humedad[i]}</td>
            </tr>
        `;
    }

    const opciones = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 10 }
                }
            }
        }
    };

    new Chart(document.getElementById('graficoLluvia'), {
        type: 'bar',
        data: {
            labels: nombres,
            datasets: [{
                label: 'Lluvia (mm)',
                data: lluvia,
                backgroundColor: '#2a5298'
            }]
        },
        options: opciones
    });

    new Chart(document.getElementById('graficoTemp'), {
        type: 'line',
        data: {
            labels: nombres,
            datasets: [{
                label: 'Temperatura °C',
                data: temperatura,
                borderColor: '#e74c3c',
                fill: false
            }]
        },
        options: opciones
    });

    new Chart(document.getElementById('graficoHumedad'), {
        type: 'pie',
        data: {
            labels: nombres,
            datasets: [{
                data: humedad,
                backgroundColor: [
                    '#1e3c72',
                    '#2a5298',
                    '#4facfe',
                    '#00c6ff',
                    '#81ecec'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 10
                        },
                        boxWidth: 12
                    }
                }
            }
        }
    });

};
