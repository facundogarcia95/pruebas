DROP TABLE IF EXISTS `cola_tareas`;
CREATE TABLE IF NOT EXISTS `cola_tareas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accion` varchar(255) NOT NULL,
  `parametros` varchar(255) DEFAULT NULL,
  `en_ejecucion` tinyint(1) NOT NULL DEFAULT '0',
  `pendiente` tinyint(1) NOT NULL DEFAULT '0',
  `md5_sum` varchar(33) NOT NULL,
  `time_start` timestamp DEFAULT NULL,
  `time_finish` timestamp DEFAULT NULL,
  `borrado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cola_tareas-en_ejecucion` (`en_ejecucion`),
  KEY `cola_tareas-pendiente` (`pendiente`),
  KEY `cola_tareas-borrado` (`borrado`),
  KEY `cola_tareas-md5_sum` (`md5_sum`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `cola_tareas` ADD  `id_usuario` INT NULL DEFAULT NULL AFTER  `id` ,
ADD INDEX (  `id_usuario` ) ;