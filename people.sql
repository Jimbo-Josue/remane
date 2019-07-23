CREATE TABLE IF NOT EXISTS `usuario` (
`idUsuario` int(11) NOT NULL AUTO_INCREMENT,
 `nombre` varchar(150) NOT NULL,
 `contrasena` varchar(255) NOT NULL,
 `claveApi` varchar(150) NOT NULL,
 `correo` varchar(255) NOT NULL UNIQUE,
 PRIMARY KEY (idUsuario)
);

CREATE TABLE IF NOT EXISTS `contacto` (
`idContacto` int(11) NOT NULL AUTO_INCREMENT,
 `primerNombre` varchar(50) NOT NULL,
 `primerApellido` varchar(50) NOT NULL,
 `telefono` varchar(20) NOT NULL,
 `correo` varchar(255) NOT NULL,
 `idUsuario` int(11) NOT NULL, 
 PRIMARY KEY (idContacto),
 FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario)
 ON UPDATE CASCADE
 ON DELETE CASCADE
);


