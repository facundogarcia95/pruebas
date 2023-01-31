# Cron-PHP

## Ejemplos


Posicionado dentro de la carpeta de ejemplos puede ejectar
```
sudo -u www-data php sample_consola.php Colatareas_simple test_colatareas '{"foo":"baz"}'
```
o
```
sudo -u www-data php sample_consola.php Consola_simple test_proceso '{"foo":"baz"}'
```
que es lo mismo que ejecutar
```
sudo -u www-data php sample_consola.php Consola_simple test_proceso '{"foo":"baz"}'
```
Donde **Colatareas_simple** es el nombre del controlador y **test_colatareas** es la accion

## Instalacion en servidores

Para el correcto funcionamiento, solo funciona en modo "Cola de Tareas"


Dentro del archivo `/etc/crontab` agregar la siguiente linea:

 > `*/1 * * * * apache /usr/bin/php74 /var/www/html/PROYECTO_X/public/cron.php cola_tareas > /tmp/cron-PROYECTO_X.log 2> /tmp/cron-PROYECTO_X.log &`


Agregar tantas lineas como proyectos existan (nunca se ejecutaran mas de 4 procesos por proyecto).


Agregar tantas lineas como instalaciones existan del proyecto.





## TODO
 - Mejorar la salida de logs. Que en produccion se pueda logguear los errores y que se eliminen los anteriores a un mes.