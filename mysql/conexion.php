<?php

class aerolinea {

// Definicion de atributos
    private $host;
    private $user;
    private $password;
    private $database;
    private $conn;

    public function __construct() {
//Constructor
        $this->host = 'localhost';
        $this->user = 'Architect02';
        $this->password = '@DM1N1STR4D0R2016';
        $this->database = 'aerolinea_yecid';
    }

    public function Conectar() {
// Metodo que crea y retorna la conexion a la BD.
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        $this->conn->set_charset("utf8");
        if ($this->conn->connect_errno) {
            die("Error al conectarse a MySQL: (" . $this->conn->connect_errno . ") " . $this->conn->connect_error);
        }
    }

    public function Cerrar() {
//Metodo que cierra la conexion a la BD
        $this->conn->close();
    }

    public function Consultar($sql) {
        /* Metodo que ejecuta un query sql
          Retorna un resultado si es un SELECT */
        $result = $this->conn->query($sql);
        return $result;
    }

    public function Contar_filas() {
        /* Metodo que retorna la cantidad de filas
          afectadas con el ultimo query realizado. */
        return $this->conn->affected_rows;
    }

    public function Resultados($result) {
        /* Metodo que retorna la ultima fila
          de un resultado en forma de arreglo. */
        return $result->fetch_assoc();
    }

    public function Liberar_resultados($result) {
//Metodo que libera el resultado del query.
        $result->free_result();
    }

}

?>