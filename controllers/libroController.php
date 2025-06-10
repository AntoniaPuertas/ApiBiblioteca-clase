<?php
//recibe los datos de una petición y devuelve una respuesta
class LibroController {
    private $libroDB;
    private $requestMethod;
    private $libroId;

    //el constructor recibe un objeto de la clase LibroDB
    //el método que se ha utilizado en la llamada: GET, POST, PUT o DELETE
    //un id de un libro que puede ser nulo
    public function __construct($db, $requestMethod, $libroId = null)
    {
        $this->libroDB = new LibroDB($db);
        $this->requestMethod = $requestMethod;
        $this->libroId = $libroId;
    }


    public function processRequest(){

        //comprobar si los datos vienen en $_POST



        //comprobar si la petición ha sido realizada con GET, POST, PUT, DELETE
        switch($this->requestMethod){
            case 'GET':
                if($this->libroId){
                    //devolver un libro
                    $respuesta = $this->getLibro($this->libroId);
                }else{
                    //libroId es nulo y devuleve todos los libros
                    $respuesta = $this->getAllLibros();
                }
                break;
            case 'POST':
                //crear un nuevo libro
                $respuesta = $this->createLibro();
                break;
            case 'PUT':
                $respuesta = $this->updateLibro($this->libroId);
                break;
            case 'DELETE':
                $respuesta = $this->deleteLibro($this->libroId);
                break;
            default:
                $respuesta = $this->noEncontradoRespuesta();
                break;
        }

            header($respuesta['status_code_header']);
            if($respuesta['body']){
                echo $respuesta['body'];
            }
    }

    private function getAllLibros(){
        //conseguir todos los libros de la tabla libros
        $libros = $this->libroDB->getAll();

        //construir la respuesta
        $respuesta['status_code_header'] = 'HTTP/1.1 200 OK';
        $respuesta['body'] = json_encode([
            'success' => true,
            'data' => $libros,
            'count' => count($libros)
        ]);
        return $respuesta;
    }

    private function getLibro($id){
        //llamo a la función que devuelve un libro o null
        $libro = $this->libroDB->getById($id);
        //comprobar si $libro es null
        if(!$libro){
            return $this->noEncontradoRespuesta();
        }
        //hay libro
        //construir la respuesta
        $respuesta['status_code_header'] = 'HTTP/1.1 200 OK';
        $respuesta['body'] = json_encode([
            'success' => true,
            'data' => $libro
        ]);
        return $respuesta;
    }

    private function createLibro(){
        //Verificar como vienen los datos: en el body(JSON) o en $_POST (formData)
        if(!empty($_POST['datos'])){
            //los datos vienen en formData y puede que venga un archivo
            $input = json_decode($_POST['datos'], true);
        }else{
           //Datos vienen en el JSON en el body
            $input = json_decode(file_get_contents('php://input'), true);
        }

        if(!$this->validarDatos($input)){
           return $this->datosInvalidosRespuesta();
        }

        //comprobar si viene la imagen y procesarla
        $nombreImagen = '';
        //Comprueba que viene un archivo 'imagen' y que no hay error al subir el archivo
        if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
            //Validar imagen
            $validacionImagen = $this->validarImagen($_FILES['imagen']);
            if(!$validacionImagen['valida']){
                //La imagen no ha pasado la validación
                return $this->imagenInvalidaRespuesta($validacionImagen['mensaje']);
            }

            //viene un archivo y es una imagen válida
            //guardar imagen en el servidor con nombre basado en el título
            $nombreNuevaImagen = $this->guardarImagen($_FILES['imagen'], $input['titulo']);
            if(!$nombreNuevaImagen){
                return $this->errorGuardarImagenRespuesta();
            }

        }

        $libro = $this->libroDB->create($input);

        if(!$libro){
            return $this->internalServerError();
        }

        //libro creado 
        //construir la respuesta
        $respuesta['status_code_header'] = 'HTTP/1.1 201 Created';
        $respuesta['body'] = json_encode([
            'success' => true,
            'data' => $libro,
            'message' => 'Libro creado con exito'
        ]);
        return $respuesta;

    }

    private function updateLibro($id){
        $libro = $this->libroDB->getById($id);
        if(!$libro){
            return $this->noEncontradoRespuesta();
        }
        //el libro existe
        //leo los datos que llegan en el body de la  petición
        $input = json_decode(file_get_contents('php://input'),true);

        // if(!$this->validarDatos($input)){
        //     return $this->datosInvalidosRespuesta();
        // }

        //el libro existe y los datos que llegan son válidos
        $libroActualizado = $this->libroDB->update($this->libroId, $input);

        if(!$libroActualizado){
            return $this->internalServerError();
        }
        //el libro se ha actualizado con éxito
        //construyo la respuesta
        $respuesta['status_code_header'] = 'HTTP/1.1 200 OK';
        $respuesta['body'] = json_encode([
            'success' => true,
            'message' => 'Libro actualizado exitosamente',
            'data' => $libroActualizado
        ]);
        return $respuesta;

    }

    private function deleteLibro($id){
        $libro = $this->libroDB->getById($id);

        if(!$libro){
            return $this->noEncontradoRespuesta();
        }

        if($this->libroDB->delete($id)){
            //libro borrado
            //construir la respuesta
            $respuesta['status_code_header'] = 'HTTP/1.1 200 OK';
            $respuesta['body'] = json_encode([
                'success' => true,
                'message' => 'Libro eliminado'
            ]);
        return $respuesta;

        }else{
            return $this->internalServerError();
        }

    }//fin delete libro


    private function validarDatos($datos){
        if(!isset($datos['titulo']) || !isset($datos['autor'])){
            return false;
        }
        //validar que la fecha sea un número de 4 dígitos, mayor a 1000 y menor que el año que viene
        $anio = $datos['fecha_publicacion'];
        $anioActual = (int)date("Y");

        if(!is_numeric($anio) || strlen((string)$anio) !== 4 || $anio < 1000 || $anio > $anioActual + 1){
            return false;
        }

        return true;
    }

    private function validarImagen($archivo){
        //Validar que el archivo recibido sea una imagen válida

        //Verificando errores de subida
        if($archivo['error'] !== UPLOAD_ERR_OK){
            return ['valida' => false, 'mensaje' => "Error al subir el archivo"];
        }

        //verificar el tamaño del archivo (1MB máximo)
        $tamanioMaximo = 1024 * 1024;
        if($archivo['size'] > $tamanioMaximo){
            return ['valida' => false, 'mensaje' => "La imagen  no puede superar 1MB"];
        }

        //Verificar tipo MIME
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if(!in_array($archivo['type'], $tiposPermitidos)){
            return ['valida' => false, 'mensaje' => "Sólo se permiten imágenes JPEG, PNG, GIF, WebP"];
        }

        //Verificar la extensión del archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if(!in_array($extension, $extensionesPermitidas)){
            return ['valida' => false, 'mensaje' => "Extensión del archivo no permitida"];
        }

        //Verificar que realmente sea una imagen
        $infoImagen = getimagesize($archivo['tmp_name']);
        if($infoImagen === false){
            return ['valida' => false, 'mensaje' => "El archivo no es una imagen válida"];
        }

        return ['valida' => true, 'mensaje' => ""];
    }

    private function noEncontradoRespuesta(){
        $respuesta['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $respuesta['body'] = json_encode([
            'success' => false,
            'error' => 'Libro no encontrado'
        ]);
        return $respuesta;
    }

    private function datosInvalidosRespuesta(){
        $respuesta['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $respuesta['body'] = json_encode([
            'success' => false,
            'error' => 'Datos de entrada inválidos. Se requiere título y autor. La fecha tiene formato (YYYY)'
        ]);
        return $respuesta;
    }

    private function internalServerError(){
        $respuesta['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
        $respuesta['body'] = json_encode([
            'success' => false,
            'error' => 'Error interno del servidor'
        ]);
        return $respuesta;
    }

    private function imagenInvalidaRespuesta($mensaje){
         $respuesta['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $respuesta['body'] = json_encode([
            'success' => false,
            'error' => 'Imagen inválida ' . $mensaje
        ]);
        return $respuesta;       
    }

    private function errorGuardarImagenRespuesta(){
         $respuesta['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
        $respuesta['body'] = json_encode([
            'success' => false,
            'error' => 'Error al guardar la imagen en el servidor'
        ]);
        return $respuesta;       
    }
}