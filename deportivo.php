<?php
session_start();
header("Access-Control-Allow-Origin: *");
$servidor = "localhost";
$usuario = "root";
$clave = "root";
$baseDeDatos = "test";

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);


if (isset($_GET['id'])) {
    $idDepor = $_GET['id'];
    

} else {
    $idDepor = 1;
}
$consulta = "SELECT * from deportivo INNER JOIN imgDepor on imgDepor.idDeportivo=deportivo.idDeportivo 
where deportivo.idDeportivo=" . $idDepor;
$sql = mysqli_query($enlace, $consulta);


$sql2 = mysqli_query($enlace, "SELECT * , usuario.nombre, usuario.imagen
    FROM comentDeportivo INNER JOIN usuario ON comentDeportivo.autor = usuario.idUsuario where idDeportivo=$idDepor ORDER BY fecha DESC");
$arr = mysqli_fetch_array($sql);

$sql3 = mysqli_query($enlace,"SELECT * FROM partida where idDeportivo =".$idDepor);
$partidas = mysqli_fetch_array($sql3);

?>

<!DOCTYPE html>
<html>

<head>
    <title>
        Aprovechamiento de Espacios Deportivos
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        
        #partidas{display: none;}
        nav {
            background-color: rgb(35, 81, 0);
        }

        .sectCom {
            height: 60vw;
            max-height: 20vw;
            overflow-y: scroll;
            overflow-x: hidden;
            border: 1px solid #ccc;
        }

        .navButton {
            flex: auto;
            text-align: center;
            text-decoration: none;
            color: white;

        }

        textarea {
            width: 100%;
        }

        .canvas2 {
            display: flex;
            align-items: center;

        }

        .canvas {
            display: flex;

        }

        .coment {
            padding: 2%;
        }

        #imgDep {
            width: 100%;
        }

        #perfil {
            width: 10vh;
            height: 10vh;
        }

        .side {
            background-color: aliceblue;
            width: 33%;
            padding: 2%;
        }

        .info {
            background-color: aliceblue;
            width: 66%;
            padding: 2%;
        }

        .footer {
            padding: 20px;
            text-align: center;
            background: green;
        }

        a {
            color: black;
            text-decoration: none;
        }
    </style>
</head>

<body style="background-color: rgba(255,255,255,0.75);">
    <nav  class="navbar navbar-expand-lg navbar-light bg-ligh">
        <a class="navButton" href="index.php">Inicio</a>
        <a class="navButton" href="buscador.php">Deportivos</a>
        <a class="navButton" disabled href="#"></a>
        <?php
        if ($_SESSION != NULL) {
            echo "<a class='navButton' href='perfil.php?myFlag'>Mi Cuenta</a><br>";
            echo "<a class='navButton' href='logout.php'>Cerrar Sesión</a>";
        } else {
            echo "<a class='navButton' href='login.php'>Inicio Sesión</a><br>";
            echo "<a class='navButton' href='signup.php'>Registrar</a>";
        }
        ?>

    </nav>
    
    <div class="container-fluid">
        <div class="row content">
            <div class="col-sm-4 sidenav">
                
                <img id="imgDep" class="img-thumbnail" src=<?php echo '"'.$arr[20].'"' ?> >
                <b>
                    <p>Calificación: <?php echo $arr[6]; ?>/5.0</p>
                </b>
                <strong>
                    Dirección:
                </strong>
                <p><?php echo $arr[2]; ?></p>
                <b>
                    <p>Horario:</p>
                </b>
                <p><?php echo $arr[3]; ?></p>
                <b>
                    <p>Instalaciones y Actividades:</p>
                </b>
                <p><?php echo $arr[4]; ?></p>
                <div class="">
                    <div id="map" style="width: 100%; height: 400px;"></div>
                    <script src="https://cdn.jsdelivr.net/npm/ol@v10.2.1/dist/ol.js"></script>
        <script>
            var arreglocoordenadas = [];
            
            $.ajax({
                url: 'https://maps.googleapis.com/maps/api/geocode/json',
                type: 'GET',
                data: {
                    address: '<?php echo $arr[2]; ?>',
                    key: 'AIzaSyCjDGDm_S9_UwCk7TBTOkP3UToE3rk3n90'
                },
                success: function(response) {
                    if (response.status === 'OK') {
                        var coordenadas = response.results[0].geometry.location;
                        arreglocoordenadas = [coordenadas.lng, coordenadas.lat];
                        
                        var coordenadasWEBMERC = ol.proj.fromLonLat(arreglocoordenadas);
                        var map = new ol.Map({
                            target: 'map',
                            layers: [
                                new ol.layer.Tile({
                                    source: new ol.source.OSM()
                                })
                            ],
                            view: new ol.View({
                                center: coordenadasWEBMERC,
                                zoom: 17
                            })
                        });

                        // Marker for the deportivo
                        var marker = new ol.Feature({
                            geometry: new ol.geom.Point(coordenadasWEBMERC)
                        });

                        var vectorSource = new ol.source.Vector({
                            features: [marker]
                        });

                        var markerVectorLayer = new ol.layer.Vector({
                            source: vectorSource,
                            style: new ol.style.Style({
                                image: new ol.style.Circle({
                                    radius: 8,
                                    fill: new ol.style.Fill({
                                        color: 'green'
                                    }),
                                    stroke: new ol.style.Stroke({
                                        color: 'white',
                                        width: 2
                                    })
                                })
                            })
                        });

                        map.addLayer(markerVectorLayer);

                        // Fetch nearby businesses from our database
                        $.ajax({
                            url: 'obtener_negocios.php',
                            type: 'GET',
                            data: {
                                deportivo_id: <?php echo $idDepor; ?>
                            },
                            success: function(businesses) {
                                businesses.forEach(function(business) {
                                    // Create a marker for each business
                                    var businessMarker = new ol.Feature({
                                        geometry: new ol.geom.Point(ol.proj.fromLonLat([business.lng, business.lat])),
                                        name: business.nombre,
                                        type: business.tipo
                                    });

                                    // Add popup for business info
                                    var element = document.createElement('div');
                                    element.innerHTML = `
                                        <div class="business-popup">
                                            <h4>${business.nombre}</h4>
                                            <p>${business.tipo}</p>
                                            <a href="negocio.php?id=${business.idNegocio}">Ver más</a>
                                        </div>
                                    `;

                                    var popup = new ol.Overlay({
                                        element: element,
                                        positioning: 'bottom-center',
                                        offset: [0, -20]
                                    });

                                    map.addOverlay(popup);

                                    // Add click handler for popup
                                    businessMarker.on('click', function(evt) {
                                        popup.setPosition(evt.coordinate);
                                    });

                                    vectorSource.addFeature(businessMarker);
                                });
                            },
                            error: function(error) {
                                console.error('Error fetching nearby businesses:', error);
                            }
                        });
                    }
                },
                error: function() {
                    console.error('Error in geocoding API.');
                }
            });
        </script>
                    
                </div>
            </div>

            <div class="col-sm-6">
                <h1><?php echo $arr[1]; ?></h1>
                <hr>

                <h4>Ingresa un comentario o avisa cuando irás de visita:</h4>
                <form role="form" method="post">
                    <div class="form-group">
                        <textarea class="form-control" name="comentario" rows="3" placeholder="Recuerda ser respetuoso en todo momento." required></textarea>
                    </div><BR>
                    <input type="submit" class="btn btn-success" name="subComm" value="Enviar comentario">
                </form>
                <br><br>
                <button id="alter" class="btn btn-warning" onclick="alternarDivs()">Ver Partidas</button>
                <br><br>
                
                <div id="coments" class="row" style="border-width: 2px; border-color: black;">
                <p> Comentarios:</p><br>
                    <?php while ($row = $sql2->fetch_assoc()) {
                        if ($row['imagen'] != NULL) {
                            $ruta = $row['imagen'];
                        } 
                        $fecha = new DateTime($row['fecha']);
                        $date = $fecha->format('d-m-Y H:i A');
                        ?>
                        <div class="col-sm-2 text-center">
                            <a href=<?php echo '"perfil.php?id='.$row['autor'].'"' ?> >
                            <img id="perfil" src=<?php echo "'$ruta'"; ?> class="rounded" height="65" width="65"
                                alt="Avatar">
                            </a>
                        </div>
                        <div class="col-sm-10">
                            <h5><b><?php echo $row['nombre']." "; ?></b><small><?php echo $date; ?></small></h5>
                            <p><?php echo $row['contenido']; ?></p>
                            <br>
                        </div>
                    <?php } ?>
                </div>
                <div id="partidas" class="row" style="border-width: 2px; border-color: black;">
                <a  href=<?php echo "'registropartida.php?id=".$idDepor."'" ?>><button id="add" class="btn btn-danger">Agregar Partida</button></a>
                <br><br>
                <p> Partidas:</p><br>
                    <?php while ($row = mysqli_fetch_array($sql3)) {
                        ?>                        
                        <div class="col-sm-10">
                            <h5><b><?php echo $row['nombrePartida']." "; ?></b></h5>
                            <p><?php echo $row['lugarPartida']; ?></p>
                            <br>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div><br>
</body>
<script>
        // Función JavaScript para alternar los divs
        function alternarDivs() {
            var div1 = document.getElementById('coments');
            var div2 = document.getElementById('partidas');
            var btn = document.getElementById('alter')
            var lab = document.getElementById('label');
            if (div1.style.display === 'none') {
                div1.style.display = 'flex';
                div2.style.display = 'none';
                btn.textContent = "Ver Partidas";
            } else {
                div1.style.display = 'none';
                div2.style.display = 'flex';
                btn.textContent = "Ver Comentarios";
            }
        }
    </script>

</html>
<?php
if (!empty($_POST["subComm"])) {

    if ($_SESSION == NULL) {
        //Redireccion por JavaScript
        echo '<script type="text/javascript">';
        echo 'window.location.href="login.php";';
        echo '</script>';
    }


    if (empty($_POST["comentario"])) {
        echo '<div class="alert alert-danger">LOS CAMPOS ESTAN VACIOS</div>';
    } else {
        $contenido = $_POST['comentario'];
        $autor = $_SESSION["id"];
        $fecha = date("Y-m-d H:i:s");
        $sql = mysqli_query($enlace, "INSERT INTO comentDeportivo(autor,contenido,fecha,idDeportivo) VALUES ($autor,'$contenido','$fecha',$idDepor)");
        echo '<script type="text/javascript">';
        echo 'window.location.href="deportivo.php?id=' . $idDepor . '";';
        echo '</script>';

    }
}
?>