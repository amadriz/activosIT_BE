<?php

    class Mysql extends Conexion
    {
        private $conexion;
		private $strquery;
		private $arrValues;

        public function __construct()
		{
			$this->conexion = new Conexion();
			$this->conexion = $this->conexion->connect();
		}

        //Insertar un registro
		public function insert(string $query, array $arrValues)
		{
            try {
                $this->strquery = $query;
                $this->arrValues = $arrValues;
                $insert = $this->conexion->prepare($this->strquery);
                $resInsert = $insert->execute($this->arrValues);
                $idInsert = $this->conexion->lastInsertId();
                $insert->closeCursor();
                return $idInsert;
            } catch (Exception $e) {
                //throw $th;
                $response = "Error: ". $e->getMessage();
                return $response;
            }
        }

        //Devuelve todos los registros
		public function select_all(string $query)
		{
            try {
                $this->strquery = $query;
                $execute = $this->conexion->query($this->strquery);
                $request = $execute->fetchall(PDO::FETCH_ASSOC); //ARRAY
                $execute->closeCursor();
                return $request;
            } catch (Exception $e) {
                $response = "Error: ". $e->getMessage();
                return $response;
            }
        }

        //Busca un registro
		public function select(string $query, array $arrValues)
		{
            try {
                $this->strquery = $query;
                $this->arrValues = $arrValues;
                $query = $this->conexion->prepare($this->strquery);
                $query->execute($this->arrValues);
                $request = $query->fetch(PDO::FETCH_ASSOC); //ARRAY
                $query->closeCursor();
                return $request;
            } catch (Exception $e) {
                $response = "Error: ". $e->getMessage();
                return $response;
            }
        }
        //Actualiza registros
        public function update(string $query, array $arrValues)
		{
            try {
                $this->strquery = $query;
                $this->arrValues = $arrValues;
                $update = $this->conexion->prepare($this->strquery);
                $resUdpate = $update->execute($this->arrValues);
                $update->closeCursor();
                return $resUdpate;
            } catch (Exception $e) {
                $response = "Error: ". $e->getMessage();
                return $response;
            }
        }

        //Eliminar un registros
		public function delete(string $query, array $arrValues)
		{
            try {
                $this->strquery = $query;
                $this->arrValues = $arrValues;
                $delete = $this->conexion->prepare($this->strquery);
                $del = $delete->execute($this->arrValues); 
                return $del;
            } catch (Exception $e) {
                $response = "Error: ". $e->getMessage();
                return $response;
            }
        }
    }

?>