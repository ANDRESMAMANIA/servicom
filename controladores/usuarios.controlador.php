<?php

class ControladorUsuarios
{

    /*=============================================
    INGRESO DE USUARIO
    =============================================*/

    static public function ctrIngresoUsuario()
    {

        if (isset($_POST["ingUsuario"])) {

            if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"])) {

                $encriptar = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                $tabla = "usuarios";

                $item = "Usuario";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

                if ($respuesta["Usuario"] == $_POST["ingUsuario"] && $respuesta["Password"] == $encriptar) {

                    if ($respuesta["Estado"] == 1) {

                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["IDUsuario"] = $respuesta["IDUsuario"];
                        $_SESSION["Nombre"] = $respuesta["Nombre"];
                        $_SESSION["Apellido"] = $respuesta["Apellido"];
                        $_SESSION["Usuario"] = $respuesta["Usuario"];
                        $_SESSION["Foto"] = $respuesta["Foto"];
                        $_SESSION["Perfil"] = $respuesta["Perfil"];

                        /*=============================================
                        REGISTRAR FECHA PARA SABER EL ÚLTIMO LOGIN
                        =============================================*/

                        date_default_timezone_set('America/La_Paz');

                        $fecha = date('Y-m-d');
                        $hora = date('H:i:s');

                        $fechaActual = $fecha . ' ' . $hora;

                        $item1 = "UltimoLogin";
                        $valor1 = $fechaActual;

                        $item2 = "IDUsuario";
                        $valor2 = $respuesta["IDUsuario"];

                        $ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

                        if ($ultimoLogin == "ok") {

                            echo '<script>

								window.location = "inicio";

							</script>';

                        }

                    } else {

                        echo '<br>
							<div class="alert alert-danger">El usuario aún no está activado</div>';

                    }

                } else {

                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';

                }

            }

        }

    }

    /*=============================================
    REGISTRO DE USUARIO
    =============================================*/

    static public function ctrCrearUsuario()
    {
        if (isset($_POST["nuevoUsuario"])) {
            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombreUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])
            ) {
                $ruta = "";

                // Verificar si se seleccionó una imagen
                if (isset($_FILES["nuevaFoto"]["tmp_name"]) && !empty($_FILES["nuevaFoto"]["tmp_name"])) {
                    list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    $directorio = "vistas/img/usuarios/" . $_POST["nuevoUsuario"];

                    mkdir($directorio, 0755);

                    if ($_FILES["nuevaFoto"]["type"] == "image/jpeg") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["nuevoUsuario"] . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }

                    if ($_FILES["nuevaFoto"]["type"] == "image/png") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["nuevoUsuario"] . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                $tabla = "usuarios";
                $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                $datos = array(
                    "Usuario" => $_POST["nuevoUsuario"],
                    "Password" => $encriptar,
                    "Nombre" => $_POST["nuevoNombreUsuario"],
                    "Apellido" => $_POST["nuevoApellidoUsuario"],
                    "Perfil" => $_POST["nuevoPerfilUsuario"],
                    "Especialidad" => (isset($_POST["nuevoEspecialidadUsuario"]) ? $_POST["nuevoEspecialidadUsuario"] : NULL),
                    "Foto" => $ruta,
                    "Telefono" => (isset($_POST["nuevoTelefonoUsuario"]) ? $_POST["nuevoTelefonoUsuario"] : NULL)
                );

                $respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                    swal({
                        type: "success",
                        title: "¡El usuario ha sido guardado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "listausuario";
                        }
                    });
                    </script>';
                } else {
                    echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error al guardar el usuario!",
                        text: "' . $respuesta . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                    </script>';
                }
            } else {
                echo '<script>
                swal({
                    type: "error",
                    title: "¡El usuario no puede ir vacío o llevar caracteres especiales!",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if(result.value){
                        window.location = "crearusuario";
                    }
                });
                </script>';
            }
        }
    }



    /*=============================================
        MOSTRAR USUARIO
        =============================================*/

    static public function ctrMostrarUsuarios($item, $valor)
    {

        $tabla = "usuarios";

        $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    EDITAR USUARIO
    =============================================*/

    static public function ctrEditarUsuario()
    {
        // Verificar si se ha enviado el formulario de edición de usuario
        if (isset($_POST["editarUsuario"])) {
            // Validar el nombre del usuario con una expresión regular
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombreUsuario"])) {
                // Inicializar la ruta de la imagen
                $ruta = $_POST["fotoActual"];

                // Verificar si se ha subido una nueva imagen
                if (isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])) {
                    // Obtener dimensiones de la imagen
                    list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;
                    $directorio = "vistas/img/usuarios/" . $_POST["editarUsuario"];

                    // Eliminar la imagen anterior si existe o crear el directorio si no existe
                    if (!empty($_POST["fotoActual"])) {
                        unlink($_POST["fotoActual"]);
                    } else {
                        mkdir($directorio, 0755);
                    }

                    // Redimensionar y guardar la imagen como JPEG si es JPEG
                    if ($_FILES["editarFoto"]["type"] == "image/jpeg") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }

                    // Redimensionar y guardar la imagen como PNG si es PNG
                    if ($_FILES["editarFoto"]["type"] == "image/png") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                // Nombre de la tabla en la base de datos
                $tabla = "usuarios";

                // Verificar si se ha proporcionado una nueva contraseña
                if ($_POST["editarPassword"] != "") {
                    // Validar la nueva contraseña con una expresión regular
                    if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])) {
                        // Encriptar la nueva contraseña
                        $encriptar = crypt($_POST["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    } else {
                        // Mostrar un mensaje de error si la contraseña no cumple con los requisitos
                        echo '<script>
                        swal({
                              type: "error",
                              title: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
                              showConfirmButton: true,
                              confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "listausuario";
                            }
                        })
                    </script>';
                        return;
                    }
                } else {
                    // Usar la contraseña actual si no se proporciona una nueva
                    $encriptar = $_POST["passwordActual"];
                }

                // Crear un arreglo con los datos del usuario
                $datos = array(
                    "Usuario" => $_POST["nuevoUsuario"],
                    "Password" => $encriptar,
                    "Nombre" => $_POST["editarNombreUsuario"],
                    "Apellido" => $_POST["editarApellidoUsuario"],
                    "Perfil" => $_POST["editarPerfilUsuario"],
                    "Especialidad" => (isset($_POST["editarEspecialidadUsuario"]) ? $_POST["editarEspecialidadUsuario"] : NULL),
                    "Foto" => $ruta,
                    "Telefono" => (isset($_POST["editarTelefonoUsuario"]) ? $_POST["editarTelefonoUsuario"] : NULL)
                );

                // Llamar a la función para editar el usuario en la base de datos
                $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

                // Mostrar un mensaje de éxito si la edición fue exitosa
                if ($respuesta == "ok") {
                    echo '<script>
                    swal({
                          type: "success",
                          title: "El usuario ha sido editado correctamente",
                          showConfirmButton: true,
                          confirmButtonText: "Cerrar"
                    }).then(function(result) {
                            if (result.value) {
                                window.location = "listausuario";
                            }
                    })
                </script>';
                }
            } else {
                // Mostrar un mensaje de error si el nombre no cumple con los requisitos
                echo '<script>
                swal({
                      type: "error",
                      title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
                      showConfirmButton: true,
                      confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if (result.value) {
                        window.location = "listausuario";
                    }
                })
            </script>';
            }
        }
    }


    /*=============================================
    BORRAR USUARIO
    =============================================*/

    static public function ctrBorrarUsuario()
    {

        if (isset($_GET["idUsuario"])) {

            $tabla = "usuarios";
            $datos = $_GET["idUsuario"];

            if ($_GET["fotoUsuario"] != "") {

                unlink($_GET["fotoUsuario"]);
                rmdir('vistas/img/usuarios/' . $_GET["usuario"]);

            }

            $respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

            if ($respuesta == "ok") {

                echo '<script>

				swal({
					  type: "success",
					  title: "El usuario ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result) {
								if (result.value) {

								window.location = "usuarios";

								}
							})

				</script>';

            }

        }

    }


}
	

