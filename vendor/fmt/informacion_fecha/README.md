# Api de Información del día | MINISTERIO DE TRANSPORTE

[![N|Solid](http://www.transporte.gob.ar/_img/logo_is_mediano_blanco.png)](http://desa.transporte.gob.ar)

App que devuelve paises, regiones, y localidades o localidad. Los parametros son los siguientes.

  - Habil: Obtiene si es un día hábil **es_habil($fecha)**
  - Feriado: Obtiene si es feriado **es_feriado($fecha)**
  - Feriado del mes: Obtiene todo los feriados del mes **ver_feriado_mes($fecha)**
  - Precipitaciones: Obtiene si hay precipitaciones. Posible respuestas true, false, y sin datos. **hay_precipitaciones($fecha)**

 *Salvo en el caso de ver_feriado_mes que el formato de la fecha es mm-yyyy los demás son todos yyyy-mm-dd
