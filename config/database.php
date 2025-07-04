<?php

//importamos el archivo config.php
require_once 'config.php';

//clase  para establecer la conexión con la base de datos
class Database {

    //guarda la conexion con la base de datos
    //la conexión con la base de datos es un objeto de tipo mysqli
    private $conexion;

    public function __construct()
    {
        $this->connect();
    }

    //Abre la conexión con la base de datos
    private function connect(){
        $host_name = 'db5018152581.hosting-data.io';
        $database = 'dbs14399127';
        $user_name = 'dbu2106078';
        $password = 'segurA3.!33';

        $this->conexion = new mysqli($host_name, $user_name, $password, $database);

          if ($this->conexion->connect_error) {
            die('<p>Error al conectar con servidor MySQL: '. $this->conexion->connect_error .'</p>');
        }
    }

    public function getConexion(){
        return $this->conexion;
    }

    public function close(){
        if($this->conexion){
            $this->conexion->close();
        }
    }
}