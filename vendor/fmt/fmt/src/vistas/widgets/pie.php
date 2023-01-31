  <?php
  $url_contacto =(isset($conf['url_contacto']))? (string) $conf['url_contacto'] : '';
  $style_transp = ($url_contacto=='') ? 'style="background-color:transparent;color:transparent;cursor:none;"' : '';
  $version = (file_exists(BASE_PATH."/version")) ? file_get_contents(BASE_PATH."/version") : '###';
  ##NOTA: Esta versión de pie.php requiere los CSS de "cdn/estiloIS/5/estilois.css"
  ?>
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-7">
          <a href="<?=$url_contacto?>" class="btn btn-info" <?=$style_transp;?> >Reportar un error</a>
        </div>
        <div class="col-md-5 text-right">
          <div class="row">
          <div class="col-md-4"></div>
            <div class="col-md-4 text-right foot_descrip">
              <div>
                Desarrollado por:
              </div>
            </div>
            <div class="col-md-4 text-left foot_dir">
              <span>
              COORDINACIÓN DE DISEÑO
              </span>
              <br/>
              <span id="sis">
              Y DESARROLLO DE SISTEMAS
              </span>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4 text-right foot_vson">
              <div title="Versión de la app">
                <i class="fa fa-code-fork" aria-hidden="true"></i>ersión:
              </div>
            </div>
            <div class="col-md-4 text-left foot_vson2">
                <div><?php echo preg_replace('/\n/','',$version); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>