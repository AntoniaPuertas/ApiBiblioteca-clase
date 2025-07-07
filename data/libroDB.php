<?php
/**
 * Clase LibroDB
 * 
 * Se encarga de gestionar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * para los libros en la base de datos.
 */
class LibroDB {

    private $db;
    private $table = 'libros';

    /**
     * Constructor de la clase LibroDB.
     * 
     * Recibe una instancia de la clase Database y obtiene la conexión a la base de datos.
     *
     * @param object $database Objeto que gestiona la conexión a la base de datos.
     */
    public function __construct($database){
        $this->db = $database->getConexion();
    }

    
    /**
     * Obtiene todos los libros de la base de datos.
     *
     * @return array Un array de arrays asociativos, donde cada array representa un libro. Devuelve un array vacío si no hay libros.
     */
    public  function getAll(){
        //construye la consulta
        $sql = "SELECT * FROM {$this->table}";

        //realiza la consulta con la función query()
        $resultado = $this->db->query($sql);

        //comprueba si hay respuesta ($resultado) y si la respuesta viene con datos
        if($resultado && $resultado->num_rows > 0){
            //crea un array para guardar los datos
            $libros = [];
            //en cada vuelta obtengo un array asociativo con los datos de una fila y lo guardo en la variable $row
            //cuando ya no quedan filas que recorrer termina el bucle
            while($row = $resultado->fetch_assoc()){
                //al array libros le añado $row 
                $libros[] = $row;
            }
            //devolvemos el resultado
            return $libros;
        }else{
            //no hay datos, devolvemos un array vacío
            return [];
        }
        
    }

    /**
     * Obtiene un libro específico por su ID.
     *
     * @param int $id El ID del libro a buscar.
     * @return array|null Un array asociativo con los datos del libro si se encuentra, o null si no existe o hay un error.
     */
    public function getById($id){
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if($stmt){
            //añado un parámetro a la consulta
            //este va en el lugar de la ? en la variable $sql
            //"i" es para asegurarnos de que el parámetro es un número entero
            $stmt->bind_param("i", $id);
            //ejecuta la consulta
            $stmt->execute();
            //lee el resultado de la consulta
            $result = $stmt->get_result();

            //comprueba si en el resultado hay datos o está vacío
            if($result->num_rows > 0){
                //devuelve un array asociativo con los datos
                return $result->fetch_assoc();
            }
            //cierra 
            $stmt->close();
        }
        //algo falló
        return null;
    }

    /**
     * Crea un nuevo libro en la base de datos.
     *
     * @param array $data Un array asociativo con los datos del libro.
     *                    - 'titulo' (string) Título del libro (obligatorio).
     *                    - 'autor' (string) Autor del libro (obligatorio).
     *                    - 'genero' (string) Género del libro (opcional).
     *                    - 'fecha_publicacion' (string) Fecha de publicación (opcional).
     *                    - 'disponible' (bool) Si el libro está disponible (opcional, por defecto true).
     *                    - 'imagen' (string) URL de la imagen de portada (opcional).
     *                    - 'favorito' (bool) Si el libro es un favorito (opcional, por defecto false).
     *                    - 'resumen' (string) Resumen del libro (opcional).
     * @return array|false Un array asociativo con los datos del libro recién creado, o false si la creación falla.
     */
    public function create($data){
  
        $sql = "INSERT INTO {$this->table} (titulo, autor, genero, fecha_publicacion, disponible, imagen, favorito, resumen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if($stmt){
            //comprobar los datos opcionales
            $genero = isset($data['genero']) ? $data['genero'] : '';
            $fecha_publicacion = isset($data['fecha_publicacion']) ? $data['fecha_publicacion'] : null;
            $disponible = isset($data['disponible']) ? (int)(bool)$data['disponible'] : 1;
            $imagen = isset($data['imagen']) ? $data['imagen'] : '';
            $favorito = isset($data['favorito']) ? (int)(bool)$data['favorito'] : 0;
            $resumen = isset($data['resumen']) ? $data['resumen'] : '';

            $stmt->bind_param(
                "sssiisis",
                $data['titulo'],
                $data['autor'],
                $genero,
                $fecha_publicacion,
                $disponible,
                $imagen,
                $favorito,
                $resumen
            );

            if($stmt->execute()){
                //obtengo el id del libro que se acaba de crear
                $id = $this->db->insert_id;
                $stmt->close();
                //devuelve todos los datos del libro que acabamos de crear
                return $this->getById($id);
            }
            $stmt->close();
        }
        return false;
    }

    /**
     * Actualiza los datos de un libro existente.
     *
     * @param int $id El ID del libro a actualizar.
     * @param array $data Un array asociativo con los nuevos datos del libro. Los campos no proporcionados no se actualizarán.
     * @return array|false Un array asociativo con los datos del libro actualizado, o false si la actualización falla o el libro no existe.
     */
    public function update($id, $data){

               $sql = "UPDATE {$this->table} SET
                titulo = ?,
                autor = ?,
                genero = ?,
                fecha_publicacion = ?,
                disponible = ?,
                imagen = ?,
                favorito = ?,
                resumen = ?
                WHERE id = ?
               ";

        //Leer los datos actuales
        $libro = $this->getById($id);
        if(!$libro){
            return false;
        }

        $titulo = isset($data['titulo']) ? $data['titulo'] : $libro['titulo'];
        $autor = isset($data['autor']) ? $data['autor'] : $libro['autor'];
        $genero = isset($data['genero']) ? $data['genero'] : $libro['genero'];
        $fecha_publicacion = isset($data['fecha_publicacion']) ? $data['fecha_publicacion'] : $libro['fecha_publicacion'];
        $disponible = isset($data['disponible']) ? (int)(bool)$data['disponible'] : $libro['disponible'];
        $imagen = isset($data['imagen']) ? $data['imagen'] : $libro['imagen'];
        $favorito = isset($data['favorito']) ? (int)(bool)$data['favorito'] : $libro['favorito'];
        $resumen = isset($data['resumen']) ? $data['resumen'] : $libro['resumen'];

        $stmt = $this->db->prepare($sql);
        if($stmt){   
            $stmt->bind_param(
                "sssiisisi",
                $titulo,
                $autor,
                $genero,
                $fecha_publicacion,
                $disponible,
                $imagen,
                $favorito,
                $resumen,
                $id
            );

            if($stmt->execute()){
                $stmt->close();
                //devuelve todos los datos del libro que acabamos de modificar
                return $this->getById($id);
            }
            $stmt->close();
        }
        return false; 
    }

    /**
     * Elimina un libro de la base de datos.
     *
     * @param int $id El ID del libro a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete($id){
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if($stmt){
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;

    }

}