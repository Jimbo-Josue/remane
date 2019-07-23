<?php

class grupousuario
{
	public static function post($peticion)
	{
		$grupousuario = file_get_contents('php://input');
        $datos = json_decode($grupousuario);
        
        $correo = $datos->correo;
        $nombre = $datos->nombre;

        return self::agregar($correo, $nombre);
	}
	/*
	public static function get($peticion)
	{
		$grupousuario = file_get_contents('php://input');
        $datos = json_decode($grupousuario);

        $nombre = $datos->nombre;

        return self::obtenerUsuariosGrupo($nombre);		
	}*/

	private function agregar($correo, $nombre)
	{
		$idUsuario = usuarios::autorizar();
		$idContacto = self::obtenerContactoAgregado($idUsuario, $correo);
		$idGrupo = self::obtenerIdGrupoCreado($idUsuario, $nombre);
		if($idContacto == 0)
		{
			http_response_code(400);
			return [
				"estado" => 2,
				"mensaje" => "El contacto no esta agregado o no esta registrado"
			];
		}
		if($idGrupo == 0)
		{
			http_response_code(400);
			return [
				"estado" => 3,
				"mensaje" => "El grupo no existe"
			];	
		}
		if(self::comprobarRepeticion($idGrupo, $idContacto))
		{
			http_response_code(400);
			return [
				"estado" => 4,
				"mensaje" => "Ya esta registrado"
			];			
		}

        try {

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            //return [$idGrupo => $idContacto];
            $comando = "INSERT INTO  grupo_usuario (idGrupo, idContacto) VALUES (?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $idGrupo);
            $sentencia->bindParam(2, $idContacto);
               
            $resultado = $sentencia->execute();
            http_response_code(200);

            if ($resultado) {
                return [
                	"estado" => 1,
                	"mensaje" => "Grupo Creado"
                ];

            } else {
                http_response_code(400);
                return [
                	"estado" => 2,
                	"mensaje"  => "Error Desconocido"
                ];
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(2, $e->getMessage());
        }

	}
	private function comprobarRepeticion($idGrupo, $idContacto)
	{
		$comando = "SELECT COUNT(*) FROM grupo_usuario WHERE idGrupo=? AND idContacto=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $idGrupo);
        $sentencia->bindParam(2, $idContacto);

        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;		
	}

	private function obtenerUsuariosGrupo($nombre)
	{

		
	}
	private function obtenerIdGrupoCreado($idUsuario, $nombre)
	{
		$comando = "SELECT idGrupo FROM grupo WHERE idUsuario=? AND nombre=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $idUsuario);
        $sentencia->bindParam(2, $nombre);

        $sentencia->execute();

        $idGrupo = $sentencia->fetchColumn(0);
        return $idGrupo;
	}
	private function obtenerContactoAgregado($idUsuario, $correo)
	{
		$comando = "SELECT idContacto FROM contacto WHERE idUsuario=? AND correo=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $idUsuario);
        $sentencia->bindParam(2, $correo);

        $sentencia->execute();

        $idContacto = $sentencia->fetchColumn(0);

        if($idContacto > 0)
        {
			$comando = "SELECT COUNT(*) FROM usuario WHERE correo=?";

	        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

	        $sentencia->bindParam(1, $correo);

	        $sentencia->execute();

			if($sentencia->fetchColumn(0) > 0)
				return $idContacto;
			else
				return 0;
        }
        else
        {
        	return 0;
        }
	}
}