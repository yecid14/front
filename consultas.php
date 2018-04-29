<?php
error_reporting(0);
require './mysql/conexion.php';
$sql = new aerolinea();
$sql->Conectar();



$ciudades = array();
$buscador = array();

function que_dia_es($fecha) {
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    return $nombredia;
}

function nombremes($mes) {
    setlocale(LC_TIME, 'spanish');
    $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
    return $nombre;
}

function SumaHoras($hora, $minutos_sumar) {
    $minutoAnadir = $minutos_sumar;
    $segundos_horaInicial = strtotime($hora);
    $segundos_minutoAnadir = $minutoAnadir * 60;
    $nuevaHora = date("H:i:s", $segundos_horaInicial + $segundos_minutoAnadir);
    return $nuevaHora;
}

//fin función


if (isset($_GET[mostrar_ciudad])) {

    if (isset($_GET[id_o])) {
        $cadena = "SELECT * FROM ciudades WHERE id !=$_GET[id_o]";
    } else {
        $cadena = "SELECT * FROM ciudades";
    }
    $result = $sql->Consultar($cadena);
    $num_r = $sql->Contar_filas($result);
    if ($num_r > 0) {
        while ($row = $sql->Resultados($result)) {
            $rowArray['id'] = $row['id'];
            $rowArray['nombre'] = $row['nombre'];

            array_push($ciudades, $rowArray);
        }
        echo json_encode($ciudades, JSON_UNESCAPED_UNICODE);
    }
}


if (isset($_POST[buscador])) {



    $cadena = "SELECT 
    ciu_org.nombre AS origen, ciu_des.nombre AS destino, trayectos.fecha_salida as fecha_salida, trayectos.id as id, trayectos.h_salida as h_salida, trayectos.duracion as duracion
FROM
    trayectos
        JOIN
    ciudades ciu_org ON (ciu_org.id = trayectos.ciudad_origen_id)
        JOIN
    ciudades ciu_des ON (ciu_des.id = trayectos.ciudad_destino_id)
    
WHERE ciudad_origen_id ='$_POST[ciudad_origen]' AND ciudad_destino_id ='$_POST[ciudad_destino]' AND fecha_salida BETWEEN '$_POST[start_date]' AND '$_POST[end_date]'";
    $result = $sql->Consultar($cadena);
    $num_r = $sql->Contar_filas($result);
    if ($num_r > 0) {
        ?>
        <h1 class="card-title text-center">VUELOS ENCONTRADOS PARA TU DESTINO.</h1>
        <p><br></p>
        <?php
        while ($row1 = $sql->Resultados($result)) {

            $fecha = $row1[fecha_salida];
            $fecha_e = explode("-", $row1[fecha_salida]);

            $dia = $fecha_e[2];
            $mes = $fecha_e[1];

            $catego = $sql->Consultar("SELECT c.denominacion as nom_categoria, c_t.disponibilidad as dispo, c_t.valor as valor FROM categorias_trayectos c_t JOIN categorias c  ON (c.id= c_t.categorias_id) WHERE trayectos_id ='$row1[id]'");
            ?>

            <style>

                .card_des{
                    font-size: 12px;
                }
                .card_des {
                    border: none;
                    background: #fff;
                    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.1);
                    -moz-box-shadow: 0 1px 1px rgba(0,0,0,.1);
                    -ms-box-shadow: 0 1px 1px rgba(0,0,0,.1);
                    -o-box-shadow: 0 1px 1px rgba(0,0,0,.1);
                    box-shadow: 1px -1px 0px rgba(0,0,0,.1);
                    margin-bottom: 30px;
                    position: relative;
                }
            </style>

            <div class="row">
                <div class="col-md-9">
                    <h1 class="card-title text-center">VIAJE A <?php echo strtoupper($row1[destino]) ?></h1>
                    <div class="row">

                        <div class="col-md-3 text-center descr">
                            <div class="form-group">
                                <h1 class="card-title"><i class="zmdi zmdi-airplane"></i> VUELO</h1>
                                <p><?php echo que_dia_es($row1[fecha_salida]) . "<br>" . $dia . " de " . nombremes($mes); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center descr">
                            <div class="form-group" >
                                <h1 class="card-title">DESDE<p><?php echo $row1[origen] ?></p></h1>
                                <p><?php echo $row1[h_salida]; ?></p>
                            </div>
                        </div>
                        <div class="col-md-1  descr">
                            <div class="form-group text-center" >Viaje<br><i>DIRECTO</i></div>
                        </div>
                        <div class="col-md-3 text-center descr">
                            <div class="form-group">
                                <h1 class="card-title">HASTA<p><?php echo $row1[destino] ?></p></h1>
                                <p><?php echo SumaHoras($row1[h_salida], $row1[duracion]); ?></p>
                            </div>
                        </div>
                        <div class="col-md-1 ">
                            <div class="form-group text-center"> Duración<br><?php echo $row1[duracion]; ?>mn.</div>
                        </div>
                    </div>


                </div>
                <div class="col-md-3">
                    <h1 class="card-title text-center">PRECIO</h1>
                    <div class="card m-0 card_des">

                        <div class="card-body">
                            <table class="table_not" style="width: 100%">
                                <?php
                                while ($row2 = $sql->Resultados($catego)) {
                                    ?>

                                    <tr>
                                        <td>CATEGORIA<br><i><?php echo $row2[nom_categoria] ?></i></td>
                                        <td>$ <?php echo number_format($row2[valor]) ?></td>
                                        <td>DISPONIBLES<br><i><?php echo $row2[dispo]; ?></i></td>
                                    </tr>

                                    <tr>
                                        <td colspan="3"><hr></td>
                                    </tr>

                                    <?php
                                }
                                ?> 
                                <tr>
                                    <td colspan="3">
                                        <button value_vuelo="<?php echo $row1[id]; ?>" data-toggle="modal" data-target="#modal_detalle" class="btn_vuelo btn btn-danger btn-sm btn-block">SELECCINAR</button>
                                    </td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <hr>


            <?php
        }
        ?>
        <script>
            $('.btn_vuelo').click(function () {

                var value_id = $(this).attr("value_vuelo");

                $.ajax({
                    type: "POST",
                    url: "/testBackend/consultas.php",
                    data: {"id_trayecto": value_id, "form": "detalle_vuelo"},
                    success: function (data)
                    {
                        $('#respuesta_detalle').html(data);
                    }
                });


            });
        </script>
        <?php
    } else {
        ?>
        <div class="alert alert-danger">
            <i class="zmdi zmdi-alert-polygon"></i>
            <strong>No encontramos vuelos para su búsqueda.</strong>
            <br>Realice una nueva búsqueda en ciudades cercanas.
        </div>
        <?php
    }
}



if (isset($_POST[form])) {



    $cadena = "SELECT 
    ciu_org.nombre AS origen, ciu_des.nombre AS destino, trayectos.fecha_salida as fecha_salida, trayectos.id as id, trayectos.h_salida as h_salida, trayectos.duracion as duracion
FROM
    trayectos
        JOIN
    ciudades ciu_org ON (ciu_org.id = trayectos.ciudad_origen_id)
        JOIN
    ciudades ciu_des ON (ciu_des.id = trayectos.ciudad_destino_id)
    
WHERE trayectos.id ='$_POST[id_trayecto]'";
    $result = $sql->Consultar($cadena);

    $row1 = $sql->Resultados($result);
    ?>
    <div class="row">
        <div class="col-md-8">
            <div class="">
                <header class="card-heading ">
                    <h2 class="card-title card-title">Informacion Personal</h2>

                </header>
                <div class="card-body">
                    <form id="info_user">
                        <div class="form-group is-empty" style="display: none" id="camp_nom">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="zmdi zmdi-account"></i></span>
                                <input type="text" class="form-control" name="nombre_c" id="nombre_add" placeholder="Nombre completo">
                            </div>
                        </div>
                        <div class="form-group is-empty">
                            <div class="input-group">
                                <input type="hidden" name="validar_info" value="ok">
                                <span class="input-group-addon"><i class="zmdi zmdi-account-calendar"></i></span>
                                <input type="text" class="form-control" name="doc_identidad" id="doc_add" placeholder="Documento de Identidad">
                            </div>
                        </div>
                        <div class="form-group is-empty" style="display: none" id="camp_f_n">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="zmdi zmdi-calendar"></i></span>

                                <input type="text" class="form-control " name="f_nacimiento" id="datepicker" placeholder="Fecha Nacimiento">
                            </div>
                        </div>


                    </form>

                </div>
                <div class="card-footer text-right">
                    <button type="button" id="btn_submit" class="btn btn-primary btn-sm btn-block">validar Información</button>
                    <button class="btn btn-success btn-sm btn-block" id="insert_user_p" style="display: none">Guardar</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h2 class="card-title text-center">Detalles del vuelo a <?php echo $row1[destino] ?></h2>
            <dl class="dl-horizontal">
                <dt style="text-align: left">Origen</dt>
                <dd><?php echo $row1[origen] ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt style="text-align: left">Destino</dt>
                <dd><?php echo $row1[destino] ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt style="text-align: left">Fecha de salida</dt>
                <dd><?php echo $row1[fecha_salida] ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt style="text-align: left">Hora de salida</dt>
                <dd><?php echo $row1[h_salida] ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt style="text-align: left">H. llegada (<i>aprox</i>)</dt>
                <dd><?php echo SumaHoras($row1[h_salida], $row1[duracion]); ?></dd>
            </dl>
        </div>

    </div>
    <div class="row" id="validar_info_usr">

    </div>

    <script>


        //btn_submit

        $('#btn_submit').click(function () {

            $.ajax({
                type: "POST",
                url: "/testBackend/consultas.php",
                data: $("#info_user").serialize(),
                success: function (data)
                {

                    $('#validar_info_usr').html(data);
                }
            });


        });


        var picker = new Pikaday({
            field: document.getElementById('datepicker'),
            firstDay: 1,
            maxDate: new Date(),
            yearRange: [2000, 2020]
        });
    </script>
    <?php
}


if (isset($_POST[validar_info])) {



    $cadena = "SELECT * FROM persona WHERE doc_identidad = '$_POST[doc_identidad]'";
    $result = $sql->Consultar($cadena);
    $num_p = $sql->Contar_filas($result);

    if ($num_p > 0) {
        $row3 = $sql->Resultados($result);
        if ($row3[estado] == "1") {
            ?>

            <script>
                $('#info_user').find('input, select').attr('disabled', 'disabled');
                $('#btn_submit').remove();
            </script>

            <div class="col-md-12">
                <h2 class="card-title">Confirmar Reserva</h2>
                <div></div>
            </div>
            <?php
        } else {
            ?>
            <div class="alert alert-danger">
                <b>El usuario se encuentra inactivo en nuestro sistema.</b>
            </div>

            <?php
        }
    } else {
        ?>
        <div class="alert alert-dismissible text-center" >
            <b>El usuario no se encuentra registrado.</b> 
            <button class="btn btn-sm btn-info" id="btn_reg">¿Registrar ahora?</button>

        </div>


        <script>


            //btn_submit

            $('#btn_reg').click(function () {

                $('#btn_reg').remove();
                $('#btn_submit').remove();
                $('#validar_info_usr').html("");
                $('#doc_add').attr('disabled', 'disabled');
                $('#insert_user_p').show();
                $('#camp_f_n').show();
                $('#camp_nom').show();


            });

            $('#insert_user_p').click(function () {

                $.ajax({
                    type: "POST",
                    url: "/testBackend/consultas.php",
                    data: {"nom": $("#nombre_add").val(), "doc": $("#doc_add").val(), "fecha": $("#datepicker").val(), "add_user": "add_user"},
                    success: function (data)
                    {
                        $('#validar_info_usr').html(data);
                    }
                });


            });


        </script>

        <?php
    }
}

if (isset($_POST[add_user])) {

    $insert = $sql->Consultar("INSERT INTO persona
(nom_persona,f_nacimiento,doc_identidad,estado)
VALUES
('$_POST[nom]','$_POST[fecha]','$_POST[doc]',1)");
    ?>
    <script>
        $('#info_user').find('input, select').attr('disabled', 'disabled');
        $('#insert_user_p').remove();
    </script>
    <?php
}
?>