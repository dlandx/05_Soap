<?php 
    session_start(); // Crear o Abrir sesión..
    $cod = filter_input(INPUT_POST, 'prov'); // Obtenr el cod de provincia (Nº cpine)
    $result_prov = "<select name='prov'>"; // Obtener las provincias
    $result_mun = "<select name='muni'>"; // Obtener los municipios
    $view_municipios = false;
    
    //$wsdl = "http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC/OVCCallejero.asmx?wsdl";
    $wsdl = "http://ovc.catastro.meh.es/ovcservweb/OVCSWLocalizacionRC/OVCCallejeroCodigos.asmx?wsdl";

    $cliente = new SoapClient($wsdl); // Conectar con el cliente...
    //var_dump($cliente->__getFunctions()); // Ver los metodos...
    //var_dump($cliente->__getTypes()); // Tipo de estructura...

    // Obtener las provincias....
    $provincias = $cliente->ObtenerProvincias(); 
    $provincias = simplexml_load_string($provincias->any); // Interpreta un string de XML en un objeto
    $provincias = $provincias->provinciero->prov;

    // Recorrer las provincias
    foreach ($provincias as $value) {
        $selected = ($cod == $value->cpine)? "selected" : ""; // Mantener selecionado...
        $result_prov .= "<option $selected value='".$value->cpine."'>$value->np</option>";
        $_SESSION["$value->cpine"] = $value->np; // Almacenamos nombre y cp de las provincias...
    }
  
    // BTN    
    switch (filter_input(INPUT_POST, 'btn')) {
        case "Seleccionar":
            $cod = filter_input(INPUT_POST, 'prov'); // Obtenemos el valor cod (Nº cpine)
            
            // Obtenemos los municipios...
            $municipio = $cliente->ObtenerMunicipiosCodigos($cod, "", "");
            $municipio = simplexml_load_string($municipio->any); // Interpreta un string de XML en un objeto
            $municipio = $municipio->municipiero->muni; // Obtenr municipios

            //http://ovc.catastro.meh.es/ovcservweb/ovcswlocalizacionrc/ovccallejerocodigos.asmx?op=ConsultaMunicipioCodigos
            // Recorremos los municipios...            
            foreach ($municipio as $datos) { 
                //$check = ($cod_muni == $datos->locat->cmc)? "selected" : ""; // Mantener selecionado...
                //$result_mun .= "<option value='".$datos->locat->cmc."'>$datos->nm</option>";
                $result_mun .= "<option value='$datos->nm'>$datos->nm</option>";
            }
            
            $view_municipios = true; // Hacemos visible el otro select...
            break;

        case "Resultado":
            // Mostrar provincia y municipio
            $cod = filter_input(INPUT_POST, 'prov');
            $municipio = filter_input(INPUT_POST, 'muni'); // Nombre municipio
            
            $info = "<b>Provincia seleccionada:</b> ".$_SESSION["$cod"]." <b>municipio:</b> $municipio";
            break;
            
        default:
            break;
    }

?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Provincias SOAP</title>
        <style>
            body,html{
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            body{
                font-family: Arial, Helvetica, sans-serif;
                font-size: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                background: #204056;
            }
            h1, h2, p {color: #bf5864;}
            .content {text-align: center;}
            
            /* Controls */
            input[type=submit], select {
                width: 12em;
                height: 2.5em;
                background: none;
                border: 1px solid #30a496;
                color: #9099a0;
                outline: none;
                font-weight: bold;
            }
            select {
                width: 15em;
                outline: none;
            }
            button {
                width: 6em;
                height: 2.5em;
                background: #30a496;
                color: white;
                border: none;
                outline: none;
            }
            .muni {
                margin: 2em;
            }
        </style>
    </head>
    <body>
        <div class="content">
            <h1>Seleccionar una provincia</h1>
        
            <form action="index.php" method="POST">
                <?=$result_prov."</select>"?>
                
                <!-- BTN Seleccionar provincia -->
                <input type="submit" name="btn" value="Seleccionar">

                <div class="muni">
                    <?php if ($view_municipios): ?>
                        <h2>Seleccione el municipio</h2>
                        <?=$result_mun."</select>"?>

                        <input type="submit" name="btn" value="Resultado">
                    <?php endif ?>
                </div>                
            </form>

            <p><?=$info ?? null ?></p>            
        </div>
    </body>
</html>
