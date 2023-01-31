# Api de Paises | MINISTERIO DE TRANSPORTE

[![N|Solid](https://www.transporte.gob.ar/_img/logo_ministerio_grande_blanco.png)](http://desa.transporte.gob.ar)

App que devuelve paises, regiones, y localidades o localidad. Los parametros son los siguientes.

  - Paises: get_paises().
  - Regiones:		get_regiones($id_pais). (el id del pais).
  - Region: 		get_region($id_region). (el id de la region). 
  - Localidades: 	get_localidades($id_region) (el id de la provincia).
  - Localidad: 		get_localidad($id_localidad) (el id de la localidad).
  - Gentilicios: 	get_gentilicios().