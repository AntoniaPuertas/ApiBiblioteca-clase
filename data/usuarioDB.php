<?php
/**
 * Se encarga de interactuar con la base de datos con la tabla usuarios
 */
class UsuarioDB {

    private $db;
    private $table = 'usuarios';
    
    //recibe una conexión ($database) a una base de datos y la mete en $db
    public function __construct($database){
        $this->db = $database->getConexion();
    }

    /**
     * Obtiene todos los usuarios
     */
    public function getAll(){
        $sql = "SELECT * FROM {$this->table}";
        $resultado = $this->db->query($sql);

        if($resultado && $resultado->num_rows > 0){
            $usuarios = [];
            while($row = $resultado->fetch_assoc()){
                $usuarios[] = $row;
            }
            return $usuarios;
        }
        return [];
    }

    /**
     * Obtiene un usuario por su ID
     */
    public function getById($id){
        $sql = "SELECT id, correo, nombre, apellido, creado, ultimo_acceso, bloqueado FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0){
                $usuario = $result->fetch_assoc();
                $stmt->close();
                return $usuario;
            }
            $stmt->close();
        }
        return null;
    }

    /**
     * Busca un usuario por su correo electrónico
     */
    public function getByEmail($correo){
        $sql = "SELECT * FROM {$this->table} WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0){
                $usuario = $result->fetch_assoc();
                $stmt->close();
                return $usuario;
            }
            $stmt->close();
        }
        return null;
    }

    /**
     * Crear un nuevo usuario
     */
    public function create($data){
        $sql = "INSERT INTO {$this->table} (correo, nombre, apellido, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            // Hashear la contraseña
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->bind_param(
                "ssss",
                $data['correo'],
                $data['nombre'],
                $data['apellido'],
                $passwordHash
            );

            if($stmt->execute()){
                $id = $this->db->insert_id;
                $stmt->close();
                return $this->getById($id);
            }
            $stmt->close();
        }
        return false;
    }

    /**
     * Actualizar datos de usuario
     */
    public function update($id, $data){
        $sql = "UPDATE {$this->table} SET correo = ?, nombre = ?, apellido = ?";
        $params = [$data['correo'], $data['nombre'], $data['apellido']];
        $types = "sss";

        // Si se proporciona nueva contraseña, incluirla en la actualización
        if(isset($data['password']) && !empty($data['password'])){
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->db->prepare($sql);
        if($stmt){
            $stmt->bind_param($types, ...$params);
            
            if($stmt->execute()){
                $stmt->close();
                return $this->getById($id);
            }
            $stmt->close();
        }
        return false;
    }

    /**
     * Eliminar un usuario
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

    /**
     * Verificar credenciales de login
     */
    public function verificarCredenciales($correo, $password){
        $usuario = $this->getByEmail($correo);
        
        if($usuario && password_verify($password, $usuario['password'])){
            // Verificar si el usuario no está bloqueado
            if($usuario['bloqueado'] == 1){
                return ['success' => false, 'mensaje' => 'Usuario bloqueado'];
            }
            
            // Actualizar último acceso
            $this->actualizarUltimoAcceso($usuario['id']);
            
            // No devolver la contraseña ni los tokens
            unset($usuario['password']);
            unset($usuario['token']);
            unset($usuario['token_recuperacion']);
            
            return ['success' => true, 'usuario' => $usuario];
        }
        
        return ['success' => false, 'mensaje' => 'Credenciales incorrectas'];
    }

    /**
     * Actualizar el último acceso del usuario
     */
    public function actualizarUltimoAcceso($id){
        $sql = "UPDATE {$this->table} SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    /**
     * Bloquear/desbloquear usuario
     */
    public function cambiarEstadoBloqueado($id, $bloqueado = 1){
        $sql = "UPDATE {$this->table} SET bloqueado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("ii", $bloqueado, $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    /**
     * Generar y guardar token de recuperación
     */
    public function generarTokenRecuperacion($correo){
        $usuario = $this->getByEmail($correo);
        if(!$usuario){
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $sql = "UPDATE {$this->table} SET token_recuperacion = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("si", $token, $usuario['id']);
            if($stmt->execute()){
                $stmt->close();
                return $token;
            }
            $stmt->close();
        }
        return false;
    }

    /**
     * Verificar token de recuperación
     */
    public function verificarTokenRecuperacion($token){
        $sql = "SELECT id, correo FROM {$this->table} WHERE token_recuperacion = ? AND token_recuperacion IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0){
                $usuario = $result->fetch_assoc();
                $stmt->close();
                return $usuario;
            }
            $stmt->close();
        }
        return null;
    }

    /**
     * Resetear contraseña usando token
     */
    public function resetearPassword($token, $nuevaPassword){
        $usuario = $this->verificarTokenRecuperacion($token);
        if(!$usuario){
            return false;
        }

        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = ?, token_recuperacion = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if($stmt){
            $stmt->bind_param("si", $passwordHash, $usuario['id']);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    /**
     * Verificar si un correo ya existe
     */
    public function correoExiste($correo, $excludeId = null){
        $sql = "SELECT id FROM {$this->table} WHERE correo = ?";
        $params = [$correo];
        $types = "s";

        if($excludeId){
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $stmt = $this->db->prepare($sql);
        if($stmt){
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            return $exists;
        }
        return false;
    }
}