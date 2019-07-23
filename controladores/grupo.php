<?php

class grupo
{
	public static function post($peticion)
	{
		$grupobody = file_get_contents('php://input');
        $grupo = json_decode($grupobody);
        $nombre = $grupo->nombre;
        return self::crear($nombre);
	}
	
	public static function get($peticion)
	{
		if($peticion[0] == 'creados')
			return self::obtenerGrupos();
		else if($peticion[0] == 'registrados')
			return self::obtenerGruposRegistrados();
	}

	private function obtenerGrupos()
	{
		$idUsuario = usuarios::autorizar();
		$comando = "SELECT nombre, tipo FROM grupo WHERE idUsuario=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $idUsuario);
        $sentencia->execute();

        http_response_code(200);

        return [
        	"estado" => 1,
        	"grupos" => $sentencia->fetchAll(PDO::FETCH_ASSOC) 
        ];
	}

	private function obtenerGruposRegistrados()
	{
		$idUsuario = usuarios::autorizar();
		$correo = self::obtenercorreo($idUsuario);

//		$comando = "SELECT nombre, tipo FROM grupo WHERE idUsuario=?";
  		$comando = "SELECT usuario.correo,grupo.nombre FROM grupo_usuario INNER JOIN grupo ".
  					"ON grupo.idGrupo=grupo_usuario.idGrupo INNER JOIN contacto ON ".
  					"contacto.idContacto=grupo_usuario.idContacto INNER JOIN usuario ON ".
  					"usuario.idUsuario=contacto.idUsuario WHERE contacto.correo=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $correo);
        $sentencia->execute();

        http_response_code(200);

        return [
        	"estado" => 1,
        	"grupos" => $sentencia->fetchAll(PDO::FETCH_ASSOC) 
        ];
	}
	private function obtenercorreo($idUsuario)
	{
        $comando = "SELECT correo FROM usuario WHERE idUsuario=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $idUsuario);

        $sentencia->execute();

        return $sentencia->fetchColumn(0);		
	}

	private function crear($nombre)
	{
		$idUsuario = usuarios::autorizar();
		
		if(self::comprobarRepeticion($idUsuario, $nombre))
		{
			http_response_code(400);
			return [
				"estado" => 2,
				"mensaje" => "Ya exite un grupo con el mismo nombre"
			];
		}

        try {

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO grupo (idUsuario, nombre, tipo) VALUES (?,?,1)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $idUsuario);
            $sentencia->bindParam(2, $nombre);
               
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
            throw new ExcepcionApi(3, $e->getMessage());
        }

	}

	private function comprobarRepeticion($idUsuario, $nombre)
	{
        $comando = "SELECT COUNT(*) FROM grupo WHERE idUsuario=? AND nombre=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $idUsuario);
        $sentencia->bindParam(2, $nombre);

        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;
	}
}
