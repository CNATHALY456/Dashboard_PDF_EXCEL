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

        try {
            let url = `https://api.open-meteo.com/v1/forecast?latitude=${ciudad.lat}&longitude=${ciudad.lon}&current=temperature_2m,relative_humidity_2m,precipitation`;

            let res = await fetch(url);
            let data = await res.json();

            if (!data.current) continue;

            nombres.push(ciudad.nombre);

            temperatura.push(parseFloat(data.current.temperature_2m.toFixed(1)));
            humedad.push(parseFloat(data.current.relative_humidity_2m.toFixed(0)));

            let lluviaValor = data.current.precipitation ?? 0;
            lluvia.push(parseFloat(lluviaValor.toFixed(1)));

        } catch (error) {
            console.log("Error con:", ciudad.nombre);
        }
    }

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

   
    const coloresTemp = temperatura.map(temp =>
        temp >= 34 ? "#e74c3c" :   // muy caliente
        temp >= 30 ? "#f39c12" :   // caliente
                      "#3498db"    // fresco
    );

    
    new Chart(document.getElementById('graficoTemp'), {
        type: 'bar',
        data: {
            labels: nombres,
            datasets: [{
                label: 'Temperatura °C',
                data: temperatura,
                backgroundColor: coloresTemp
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });

 
    let promedio = temperatura.reduce((a, b) => a + b, 0) / temperatura.length;

    new Chart(document.getElementById('graficoCalorPromedio'), {
        type: 'doughnut',
        data: {
            labels: ['Calor', 'Restante'],
            datasets: [{
                data: [promedio, 40 - promedio],
                backgroundColor: ['#e74c3c', '#ecf0f1']
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            }
        }
    });

   
    new Chart(document.getElementById('graficoLluvia'), {
        type: 'line',
        data: {
            labels: nombres,
            datasets: [{
                label: 'Lluvia (mm)',
                data: lluvia,
                borderColor: '#2a5298',
                fill: false
            }]
        }
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
        }
    });

};

function generarPDF() {

    const temp = document.getElementById("graficoTemp").toDataURL("image/png");
    const lluvia = document.getElementById("graficoLluvia").toDataURL("image/png");
    const humedad = document.getElementById("graficoHumedad").toDataURL("image/png");
    const promedio = document.getElementById("graficoCalorPromedio").toDataURL("image/png");

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "generar_pdf.php";

    function agregarCampo(nombre, valor) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
    }

    agregarCampo("graficoTemp", temp);
    agregarCampo("graficoLluvia", lluvia);
    agregarCampo("graficoHumedad", humedad);
    agregarCampo("graficoCalorPromedio", promedio);

    document.body.appendChild(form);
    form.submit();
}

function generarExcel(e) {

    e.preventDefault();

    const temp = document.getElementById("graficoTemp").toDataURL("image/png");
    const lluvia = document.getElementById("graficoLluvia").toDataURL("image/png");
    const humedad = document.getElementById("graficoHumedad").toDataURL("image/png");
    const promedio = document.getElementById("graficoCalorPromedio").toDataURL("image/png");

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "exportar_excel.php";

    function campo(nombre, valor) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
    }

    campo("graficoTemp", temp);
    campo("graficoLluvia", lluvia);
    campo("graficoHumedad", humedad);
    campo("graficoCalorPromedio", promedio);

    document.body.appendChild(form);
    form.submit();
}