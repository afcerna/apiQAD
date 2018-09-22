<?php
/*

genera_ov2.php
Versión 0.1.1.
Fecha Creación: 06/12/2011

 -------------------------------------------------------------------------------------------------------------------------------
| Responsable   Hora Cambio     Acción                                                                                          |
|-------------|---------------|-------------------------------------------------------------------------------------------------|
| CMM         | 20111206_1930 | Inclusión de versionado y control de cambios.                                                   |
 -------------------------------------------------------------------------------------------------------------------------------


*/
?>
<?php include("../lib/functions.php");?>
<?php require_once('../../connections/cotiza.php'); ?>
<?php require_once('../../clases_comunes/classPHPLog.php'); ?>
<?php
noCache();

$log 			= new classPHPLog();
$_LOG_PHP_PAGE	= "genera_ov2.php";
$log -> info($_LOG_PHP_PAGE, "---------- Inicio ".$_LOG_PHP_PAGE." ----------");


function retorna_tipo_bolsa($bl)
{
	$query    = "SELECT * FROM bolsa_mstr WHERE bolsa_id = '$bl'";
	$rslt_bls = mysql_query($query,$GLOBALS['cotiza']);
	$row_bls  = mysql_fetch_assoc($rslt_bls);
	return ($row_bls['bolsa_desc']);
}

function genera_version($v) 	
{
	for($i=strlen($v);$i<3;$i++)
		$nv .="0";
	return $nv.$v;
}

function asigna_numero($v)
{
	if(is_numeric($v))	
		return $v;
	return 0;
}
$id_solicitud 	= $_GET['id_solicitud'];
$acc			= $_GET['acc'];
$tipo_bolsa		= retorna_tipo_bolsa($row['solic_bolsa']);
$paso_taca		= retorna_valor_proc($id_solicitud,3,6);

mysql_select_db($database_cotiza, $cotiza);

$log -> info($_LOG_PHP_PAGE, "id_solicitud: ".$id_solicitud);

$query = "SELECT * FROM ov_view WHERE solic_id = '$id_solicitud'";
$rsl   = mysql_query($query,$cotiza);
$row   = mysql_fetch_assoc($rsl);

$serie = $row['solic_serie'];
$tprod = $row['solic_tprod'];
$mat   = $row['solic_material'];
$ruta  = $row['solic_ruta'];

if($row['solic_exportacion'])
{
	$exportacion 		= "S";
	$det_exportacion	= "EXPO";
}
else
{
	$exportacion 		= "N";
	$det_exportacion	= "NAC";
}
	
$query     = "SELECT solic_mstr.solic_serie, Max(solic_mstr.solic_version) AS solic_version
				FROM solic_mstr
			   WHERE solic_mstr.solic_version <>  '' 
			     AND solic_mstr.solic_serie = '$serie'
			GROUP BY solic_mstr.solic_serie";
$rsl_serie = mysql_query($query,$cotiza);
$row_serie = mysql_fetch_assoc($rsl_serie);

$query	   = "SELECT * FROM result_mstr WHERE result_solic_id = '$id_solicitud'";
$rslt_cono = mysql_query($query,$cotiza);
$row_cono  = mysql_fetch_assoc($rslt_cono);

	if($row['solic_tipo'] == 2)
		$version   = $row['solic_version'];
	else
		$version   = genera_version($row_serie['solic_version'] + 1);

if($acc == "generar")
{
	$path_cm   = "/u1/shares/cotizador/cm_mstr.csv";
	$path_ov   = "/u1/shares/cotizador/so_mstr.csv";
	$path_ov_d = "/u1/shares/cotizador/sod_det.csv";
	$path_pt   = "/u1/shares/cotizador/pt_mstr.csv";
	$path_um   = "/u1/shares/cotizador/um_mstr.csv";
	$path_csv  = "/u1/shares/cotizador/sku_cliente.csv";
	
	
	
	$path_cm_log   = "/u1/shares/cotizador/cm_mstr.log";
	$path_ov_log   = "/u1/shares/cotizador/so_mstr.log";
	$path_ov_d_log = "/u1/shares/cotizador/sod_det.log";
	$path_pt_log   = "/u1/shares/cotizador/pt_mstr.log";
	$path_um_log   = "/u1/shares/cotizador/um_mstr.log";
	$path_csv_log  = "/u1/shares/cotizador/sku_cliente.log";
	
	
	$log7_1_1  = "/u1/shares/cotizador/log/7-1-1-ok.log";
	$log7_1_1_e= "/u1/shares/cotizador/log/7-1-1-err.log";
	
	$n_orden     = "OV".substr($id_solicitud,1);
	$cliente     = $_POST['cliente'];
	$embarcar    = $_POST['embarcar'];
	$fecha       = $_POST['fecha'];
	$articulo    = $_POST['articulo'];
	$precio      = $_POST['precio'];
	$costo       = $_POST['costo'];
	$cantidad    = $_POST['cantidad'];
	$um		     = $_POST['um'];
	$nombre      = $_POST['nombre'];
	$moneda		 = $_POST['moneda'];
	$cambio		 = $_POST['tipo_cambio'];
	$facturar    = $_POST['facturar'];
	$flete		 = asigna_numero($_POST['flete']);
	$peso_cono   = asigna_numero(number_format($row_cono['result_peso_cono'],5,".",""));
	$um_cono     = "KG";
	$peso_unitario = $_POST['peso_unitario'];
	$cst_clisse  = asigna_numero($_POST['costo_clisse']);
	$car_cliente = asigna_numero($_POST['cargo_cliente']);
	$car_eroflex = asigna_numero($_POST['cargo_eroflex']);
	$costo       = asigna_numero($_POST['costo']);
	$comision    = asigna_numero($_POST['comision']);
	$margen      = asigna_numero($_POST['margen']);
	$precio      = asigna_numero($_POST['precio']);
	$moneda      = $_POST['moneda'];
	$dp			 = $_POST['dolarpeso'];
	$flete_tot   = number_format(($row_cono['result_peso_pedido'] * $row_cono['result_flete']),0,',','');
	$tipo_cambio = asigna_numero($_POST['tipo_cambio']);
	$c1			 = asigna_numero(str_replace(".","",$_POST['cant_det_1']));
	$c2			 = asigna_numero(str_replace(".","",$_POST['cant_det_2']));
	$c3			 = asigna_numero(str_replace(".","",$_POST['cant_det_3']));
	$c4			 = asigna_numero(str_replace(".","",$_POST['cant_det_4']));
	$c5			 = asigna_numero(str_replace(".","",$_POST['cant_det_5']));
	$c6			 = asigna_numero(str_replace(".","",$_POST['cant_det_6']));
	$c7			 = asigna_numero(str_replace(".","",$_POST['cant_det_7']));
	$c8			 = asigna_numero(str_replace(".","",$_POST['cant_det_8']));
	$c9			 = asigna_numero(str_replace(".","",$_POST['cant_det_9']));
	$c10		 = asigna_numero(str_replace(".","",$_POST['cant_det_10']));
	
	$f1			 = $_POST['fecha_det_1'];
	$f2			 = $_POST['fecha_det_2'];
	$f3			 = $_POST['fecha_det_3'];
	$f4			 = $_POST['fecha_det_4'];
	$f5			 = $_POST['fecha_det_5'];
	$f6			 = $_POST['fecha_det_6'];
	$f7			 = $_POST['fecha_det_7'];
	$f8			 = $_POST['fecha_det_8'];
	$f9			 = $_POST['fecha_det_9'];
	$f10		 = $_POST['fecha_det_10'];
	
	
	/*Se salvan los campos*/
	
	$query = "INSERT INTO result_mstr(result_solic_id, result_costo_clisse, result_cargo_cliente, 
									  result_cargo_eroflex, result_costo, result_margen, result_precio, 
					 				  result_moneda, result_dp, result_tipo_cambio, result_comision, result_flete, result_peso_prod_br)
  				   VALUES ('$id_solicitud', $cst_clisse, $car_cliente, $car_eroflex, $costo, $margen, $precio, 
						   '$moneda', '$dp', $tipo_cambio, $comision, $flete, '$peso_unitario')
  ON DUPLICATE KEY UPDATE 
	                 result_costo_clisse = $cst_clisse, 
				     result_cargo_cliente = $car_cliente, result_cargo_eroflex = $car_eroflex, 
					 result_costo = $costo, result_margen = $margen, result_precio = $precio, 
					 result_moneda = '$moneda', result_dp='$dp', result_tipo_cambio = $tipo_cambio, result_comision = $comision,
					 result_flete  = $flete, result_peso_prod_br='$peso_unitario'";
	mysql_query($query,$cotiza) or die(mysql_error());
	
	
	
	// Se Salva el detalle de las lineas
	$query = "INSERT INTO detalle_ov (detalle_solic_id, detalle_c1, detalle_f1, detalle_c2, detalle_f2, detalle_c3,
									  detalle_f3, detalle_c4, detalle_f4, detalle_c5, detalle_f5, detalle_c6,
									  detalle_f6, detalle_c7, detalle_f7, detalle_c8, detalle_f8, detalle_c9,
									  detalle_f9, detalle_c10, detalle_f10)
							  VALUES ('$id_solicitud',$c1,'$f1',$c2,'$f2',$c3,'$f3',$c4,'$f4',$c5,'$f5',$c6,'$f6',
									  $c7,'$f7',$c8,'$f8',$c9,'$f9',$c10,'$f10')
			 ON DUPLICATE KEY UPDATE  detalle_c1 = $c1, detalle_f1 = '$f1', detalle_c2 = $c2, detalle_f2 = '$f2',
			                          detalle_c3 = $c3, detalle_f3 = '$f3', detalle_c4 = $c4, detalle_f4 = '$f3',
									  detalle_c5 = $c5, detalle_f5 = '$f5', detalle_c6 = $c6, detalle_f6 = '$f6', 
									  detalle_c7 = $c7, detalle_f7 = '$f7', detalle_c8 = $c8, detalle_f8 = '$f8',
									  detalle_c9 = $c9, detalle_f9 = '$f9', detalle_c10= $c10,detalle_f10= '$f10'";
	mysql_query($query,$cotiza) or die (mysql_error());


	//se actualiza la fecha de creacion de la OV
	$fecha2 = date("Y-m-d");
	$query = "UPDATE solic_mstr 
	             SET solic_cantidad  = $cantidad, 
				     solic_fecha_ov  = '$fecha2',
					 solic_npcliente = '$nombre'
			   WHERE solic_id = '$id_solicitud'";
	mysql_query($query,$cotiza);
	
	$cnt_det[0] = str_replace(".","",$_POST['cant_det_1']);
	$cnt_det[1] = str_replace(".","",$_POST['cant_det_2']);
	$cnt_det[2] = str_replace(".","",$_POST['cant_det_3']);
	$cnt_det[3] = str_replace(".","",$_POST['cant_det_4']);
	$cnt_det[4] = str_replace(".","",$_POST['cant_det_5']);
	$cnt_det[5] = str_replace(".","",$_POST['cant_det_6']);
	$cnt_det[6] = str_replace(".","",$_POST['cant_det_7']);
	$cnt_det[7] = str_replace(".","",$_POST['cant_det_8']);
	$cnt_det[8] = str_replace(".","",$_POST['cant_det_9']);
	$cnt_det[9] = str_replace(".","",$_POST['cant_det_10']);
	
	$fec_det[0] = $_POST['fecha_det_1'];
	$fec_det[1] = $_POST['fecha_det_2'];
	$fec_det[2] = $_POST['fecha_det_3'];
	$fec_det[3] = $_POST['fecha_det_4'];
	$fec_det[4] = $_POST['fecha_det_5'];
	$fec_det[5] = $_POST['fecha_det_6'];
	$fec_det[6] = $_POST['fecha_det_7'];
	$fec_det[7] = $_POST['fecha_det_8'];
	$fec_det[8] = $_POST['fecha_det_9'];
	$fec_det[9] = $_POST['fecha_det_10'];
	

	$query = "SELECT result_comision, result_dp, result_margen FROM result_mstr WHERE result_solic_id='$id_solicitud'";
	$rslt_comision = mysql_query($query, $cotiza);
	$row_comision  = mysql_fetch_assoc($rslt_comision);
	
	$comision	= $row_comision['result_comision'];
	$dolarpeso	= $row_comision['result_dp'];
	//$margen     = $row_comision['result_margen'];
	
	$query = "SELECT solic_sku, solic_oc, solic_cliente, solic_ruta, solic_cantidad FROM solic_mstr WHERE solic_id='$id_solicitud'";
	$rslt_comision = mysql_query($query, $cotiza);
	$row_comision  = mysql_fetch_assoc($rslt_comision);
	
	$sku_cliente	= $row_comision['solic_sku'];
	$oc_cliente		= $row_comision['solic_oc'];
	$rut_cliente	= $row_comision['solic_cliente'];
	$solic_ruta		= $row_comision['solic_ruta'];
	$solic_cantidad	= $row_comision['solic_cantidad'];
	
	
	$query = "SELECT cm_giro FROM cm_mstr WHERE cb_rut='$rut_cliente'";
	$rslt_giro = mysql_query($query, $cotiza);
	$row_giro  = mysql_fetch_assoc($rslt_giro);
	
	$giro_cliente	= $row_giro['cm_giro'];
	
	
	if (trim($comision)=='')
	{
		$comision	= "0";
	}
	else
	{
		$comision	= str_replace(".", ",", $comision);
	}
	
	
	if (trim($cambio)=='')
	{
		$cambio	= "1";
	}
	else
	{
		$cambio	= str_replace(".", ",", $cambio);
	}
	
	if (trim($sku_cliente)=='')
	{
		$sku_cliente	= "SKU";
	}
	
	if (trim($oc_cliente)=='')
	{
		$oc_cliente	= "OC0";
	}
	
	if (trim($giro_cliente)=='')
	{
		$giro_cliente	= "SIN GIRO";
	}
	

	$log -> info($_LOG_PHP_PAGE, "Generando ".$path_ov);
	$ov = fopen($path_ov,'w+');
	fputs($ov,$cliente.";");
	fputs($ov,$cliente.";");
	fputs($ov,$embarcar.";");
	fputs($ov,$fecha.";");
	fputs($ov,$n_orden.";");
	fputs($ov,$moneda.";");
	fputs($ov,$cambio.";");
	fputs($ov,$exportacion.";");			//	ESTE ES EL CAMPO QUE REGISTRA SI ES O NO GRAVABLE
	fputs($ov,$flete_tot.";");
	fputs($ov,$sku_cliente.";");
	fputs($ov,$oc_cliente.";");
	fputs($ov,$comision.";");
	fputs($ov,$giro_cliente.";");
	fputs($ov,$solic_ruta.";");
	fputs($ov,$det_exportacion.";");		//	ESTE ES EL CAMPO QUE REGISTRA "EXPO" O "NAC"
	fputs($ov,$dolarpeso.";");
	fputs($ov,$margen.";");
	fclose($ov);
	
	$ov = fopen($path_ov_log,'a+');
	fputs($ov,$cliente.";");
	fputs($ov,$cliente.";");
	fputs($ov,$embarcar.";");
	fputs($ov,$fecha.";");
	fputs($ov,$n_orden.";");
	fputs($ov,$moneda.";");
	fputs($ov,$cambio.";");
	fputs($ov,$exportacion.";");			//	ESTE ES EL CAMPO QUE REGISTRA SI ES O NO GRAVABLE
	fputs($ov,$flete_tot.";");
	fputs($ov,$sku_cliente.";");
	fputs($ov,$oc_cliente.";");
	fputs($ov,$comision.";");
	fputs($ov,$giro_cliente.";");
	fputs($ov,$solic_ruta.";");
	fputs($ov,$det_exportacion.";");		//	ESTE ES EL CAMPO QUE REGISTRA "EXPO" O "NAC"
	fputs($ov,$dolarpeso.";");
	fputs($ov,$margen.";\n");
	fclose($ov);
	
	
	
	
	$log -> info($_LOG_PHP_PAGE, "Generando ".$path_csv);
	$aux_rut	= explode("-", $rut_cliente);
	$pt_aux 	= fopen($path_csv, "w");
	fputs($pt_aux, $aux_rut[0].";");
	fputs($pt_aux, $sku_cliente.";");
	fputs($pt_aux, $articulo.";");
	fputs($pt_aux, $nombre.";\n");
	fclose($pt_aux);
	
	$pt_aux 	= fopen($path_csv_log, "a+");
	fputs($pt_aux, $aux_rut[0].";");
	fputs($pt_aux, $sku_cliente.";");
	fputs($pt_aux, $articulo.";");
	fputs($pt_aux, $nombre.";\n");
	fclose($pt_aux);
	
	if($facturar == "KG")
		$bon = "B";
	elseif($facturar == "KN")
		$bon = "N";
	else
		$bon = 0; 
		
	if ($moneda == 'CLP')
	{
		$total_decimales = 2;
	}
	else
	{
		$total_decimales = 5;
	}
	
	$precio_sod_det	= number_format($precio, $total_decimales, ",", ".");
	//$precio_sod_det	= $precio;
	
	//Genera el factor de conversiï¿½n de unidad a KG
	if($um == "UN")
	{
		$log -> info($_LOG_PHP_PAGE, "Iniciando cï¿½lculos de pecio y costo para UN ");
		
		$query   	= "SELECT * FROM result_mstr WHERE result_solic_id = '$id_solicitud'";
		$rslt_re 	= mysql_query($query, $cotiza);
		$row_re  	= mysql_fetch_assoc($rslt_re);
		$factor		= $row_re['result_peso_prod_br'] / 1000;
		$peso_total	= $factor * $solic_cantidad;
		$precio_total	= $precio * $solic_cantidad;
		$costo_total	= $costo * $solic_cantidad;
		$fc		 	= number_format($factor,6,",",".");
		
		$log -> debug($_LOG_PHP_PAGE, "Valor en result_peso_prod_br (Peso Unitario): ".$row_re['result_peso_prod_br']." [gr]");
		$log -> debug($_LOG_PHP_PAGE, "factor: ".$factor);
		$log -> debug($_LOG_PHP_PAGE, "peso_total: ".$peso_total." [kg]");
		//$log -> info($_LOG_PHP_PAGE, "fc: ".$fc);
		
		
		$log -> info($_LOG_PHP_PAGE, "Generando ".$path_um);
		$om 		= fopen($path_um,'w+');
		fputs($om,"KG;");
		fputs($om,"UN;");
		fputs($om,$articulo.";");
		fputs($om,$fc.";");
		fclose($om);
		
		$om 		= fopen($path_um_log,'a+');
		fputs($om,"KG;");
		fputs($om,"UN;");
		fputs($om,$articulo.";");
		fputs($om,$fc.";\n");
		fclose($om);
		
		
		$log -> debug($_LOG_PHP_PAGE, "Precio Inicial: ".$precio);
		$log -> debug($_LOG_PHP_PAGE, "Costo Inicial: ".$costo);
		
		
		if ( $factor*1 != 0)
		{
			//$precio		= $precio / $factor;
			//$costo		= $costo / $factor;
			//$precio		= number_format($precio / $factor,6,".","");
			//$costo		= number_format($costo / $factor,6,".","");
			
			$log -> debug($_LOG_PHP_PAGE, "Precio Sin Formato: ".$precio_total / $peso_total);
			$log -> debug($_LOG_PHP_PAGE, "Costo Sin Formato: ".$costo_total / $peso_total);
		
			//$precio		= number_format($precio_total / $peso_total, $total_decimales,",",".");
			//$costo		= number_format($costo_total / $peso_total, $total_decimales,",",".");
			
			$precio		= $precio_total/$peso_total;
			$costo		= $costo_total/$peso_total;
		}
		else
		{
			$precio = 0;
			$costo = 0;
		}

		$log -> debug($_LOG_PHP_PAGE, "Precio KG (Sin Formato): ".$precio);
		$log -> debug($_LOG_PHP_PAGE, "Costo KG  (Sin Formato): ".$costo);

	}
	
	$precio		= number_format($precio, $total_decimales,",",".");
	$costo		= number_format($costo, $total_decimales,",",".");
	
	$log -> debug($_LOG_PHP_PAGE, "Precio KG: ".$precio);
	$log -> debug($_LOG_PHP_PAGE, "Costo KG : ".$costo);

	
	
	//genera detalle de la OV
	$log -> info($_LOG_PHP_PAGE, "Generando ".$path_ov_d);
	$ln = 1;
	$ov_det = fopen($path_ov_d,'w+');
	for($i=0;$i<10;$i++)
		if($cnt_det[$i] != "" && $cnt_det[$i] != "0")
		{			
			$log -> debug($_LOG_PHP_PAGE, "Precio en sod_det.csv: ".$precio_sod_det);
			$log -> info($_LOG_PHP_PAGE, "Generando Lï¿½nea ".$ln);
			fputs($ov_det,$n_orden.";");
			fputs($ov_det,$ln.";");
			fputs($ov_det,$articulo.";");
			fputs($ov_det,$um.";");
			fputs($ov_det,$precio_sod_det.";");
			fputs($ov_det,$cnt_det[$i].";");
			fputs($ov_det,$fec_det[$i].";");
			fputs($ov_det,$bon.";\n");
			
			//$log -> info($_LOG_PHP_PAGE, "Generando Lï¿½nea ".$ln."1");
			//fputs($ov_det,$n_orden.";");
			//fputs($ov_det,$ln."1;");
			//fputs($ov_det,$articulo.";");
			//fputs($ov_det,$um.";");
			//fputs($ov_det,$precio_sod_det.";");
			//fputs($ov_det,$cnt_det[$i]*0.5.";");
			//fputs($ov_det,$fec_det[$i].";");
			//fputs($ov_det,$bon.";\n");
			
			$ln++;
		}
	fclose($ov_det);
	
	$ln = 1;
	$ov_det = fopen($path_ov_d_log,'a+');
	for($i=0;$i<10;$i++)
		if($cnt_det[$i] != "" && $cnt_det[$i] != "0")
		{			
			//$log -> debug($_LOG_PHP_PAGE, "Precio en sod_det.csv: ".$precio_sod_det);
			//$log -> info($_LOG_PHP_PAGE, "Generando Lï¿½nea ".$ln);
			fputs($ov_det,$n_orden.";");
			fputs($ov_det,$ln.";");
			fputs($ov_det,$articulo.";");
			fputs($ov_det,$um.";");
			fputs($ov_det,$precio_sod_det.";");
			fputs($ov_det,$cnt_det[$i].";");
			fputs($ov_det,$fec_det[$i].";");
			fputs($ov_det,$bon.";\n");
			
			////$log -> info($_LOG_PHP_PAGE, "Generando Lï¿½nea ".$ln."1");
			//fputs($ov_det,$n_orden.";");
			//fputs($ov_det,$ln."1;");
			//fputs($ov_det,$articulo.";");
			//fputs($ov_det,$um.";");
			//fputs($ov_det,$precio_sod_det.";");
			//fputs($ov_det,$cnt_det[$i]*0.5.";");
			//fputs($ov_det,$fec_det[$i].";");
			//fputs($ov_det,$bon.";\n");
			
			$ln++;
		}
	fclose($ov_det);
	
	
	
	$log -> debug($_LOG_PHP_PAGE, "Precio en pt_mstr.csv: ".$precio);
	$log -> debug($_LOG_PHP_PAGE, "Costo en pt_mstr.csv: ".$costo);
	
	
	
	$log -> info($_LOG_PHP_PAGE, "Generando ".$path_pt);
	$pt = fopen($path_pt,'w+');								//	/u1/shares/cotizador/pt_mstr.csv
	fputs($pt,$articulo.";");								//	01
	fputs($pt,"KG;");										//	02
	fputs($pt,$nombre.";");									//	03
	fputs($pt,"PTERM".";");									//	04
	fputs($pt,"PTERM".";");									//	05
	fputs($pt,number_format($peso_cono,5,',','').";");		//	06
	fputs($pt,$um_cono.";");								//	07
	fputs($pt,$precio.";");									//	08		
	fputs($pt,$costo.";");									//	09		
	fputs($pt,$tprod.";");//tipo							//	10
	fputs($pt,$mat.";");//material							//	11
	fputs($pt,$ruta.";");//ruta								//	12
	fputs($pt,$sku_cliente.";");							//	13
	fputs($pt,$moneda.";");									//	14
	fclose($pt);
	
	$pt = fopen($path_pt_log,'a+');							//	/u1/shares/cotizador/pt_mstr.csv
	fputs($pt,$articulo.";");								//	01
	fputs($pt,"KG;");										//	02
	fputs($pt,$nombre.";");									//	03
	fputs($pt,"PTERM".";");									//	04
	fputs($pt,"PTERM".";");									//	05
	fputs($pt,number_format($peso_cono,5,',','').";");		//	06
	fputs($pt,$um_cono.";");								//	07
	fputs($pt,$precio.";");									//	08		
	fputs($pt,$costo.";");									//	09		
	fputs($pt,$tprod.";");//tipo							//	10
	fputs($pt,$mat.";");//material							//	11
	fputs($pt,$ruta.";");//ruta								//	12
	fputs($pt,$sku_cliente.";");							//	13
	fputs($pt,$moneda.";\n");								//	14
	
	
	
	if($row['cm_nuevo'])					//	CLIENTE NUEVO
	{
		$log -> info($_LOG_PHP_PAGE, "Generando ".$path_cm);
		$cm = fopen($path_cm,'w+');
		fputs($cm,$row['cm_addr'].";");
		fputs($cm,$row['cm_sort'].";");
		fputs($cm,$row['cm_direccion'].";");
		fputs($cm,$row['cm_ciudad'].";");
		fputs($cm,$row['cm_comuna'].";");
		fputs($cm,$row['cm_region'].";");
		fputs($cm,$row['cm_pais'].";");
		fputs($cm,$row['cm_contacto'].";");
		fputs($cm,$row['cm_telefono'].";");
		fputs($cm,$row['cm_fax'].";");
		fputs($cm,$row['cm_email'].";");
		fputs($cm,$row['cm_vendedor'].";");
		fputs($cm,$row['cm_moneda'].";");
		fputs($cm,$row['cm_rut'].";");
		fputs($cm,$row['cm_cr_terms'].";");
		fclose($cm);
		
		$cm = fopen($path_cm_log,'a+');
		fputs($cm,$row['cm_addr'].";");
		fputs($cm,$row['cm_sort'].";");
		fputs($cm,$row['cm_direccion'].";");
		fputs($cm,$row['cm_ciudad'].";");
		fputs($cm,$row['cm_comuna'].";");
		fputs($cm,$row['cm_region'].";");
		fputs($cm,$row['cm_pais'].";");
		fputs($cm,$row['cm_contacto'].";");
		fputs($cm,$row['cm_telefono'].";");
		fputs($cm,$row['cm_fax'].";");
		fputs($cm,$row['cm_email'].";");
		fputs($cm,$row['cm_vendedor'].";");
		fputs($cm,$row['cm_moneda'].";");
		fputs($cm,$row['cm_rut'].";");
		fputs($cm,$row['cm_cr_terms'].";\n");
		fclose($cm);
	}
	
	
	$log -> info($_LOG_PHP_PAGE, "Archivos .csv generados correctamente.");
	
	$log -> info($_LOG_PHP_PAGE, "Ejecutado /u1/users/pfs/cotizador/load_cim1-4-1.sh [".shell_exec("sh /u1/users/pfs/cotizador/load_cim1-4-1.sh")."]");
	$log -> info($_LOG_PHP_PAGE, "Ejecutado /u1/users/pfs/cotizador/load_cim1-13.sh [".shell_exec("sh /u1/users/pfs/cotizador/load_cim1-13.sh")."]");
	$log -> info($_LOG_PHP_PAGE, "Ejecutado /u1/users/pfs/cotizador/load_cim7-1-1.sh [".shell_exec("sh /u1/users/pfs/cotizador/load_cim7-1-1.sh")."]");
	
	if(file_exists($log7_1_1_e))
	{
		$log_file = fopen($log7_1_1_e,"r");
		$msg = fgets($log_file);
		fclose($log_file);
		$err = 1;
		unlink($log7_1_1_e);
		$log -> error($_LOG_PHP_PAGE, $msg);
	}
	elseif(file_exists($log7_1_1))
	{
		$log_file = fopen($log7_1_1,"r");
		$msg = fgets($log_file);
		fclose($log_file);
		$err = -1;
		unlink($log7_1_1);
		$log -> error($_LOG_PHP_PAGE, $msg);

		//$version   = genera_version($row_serie['solic_version'] + 1);
		
		$query = "UPDATE solic_mstr 
		             SET solic_status = 5, solic_version = '$version' 
				   WHERE solic_id = '$id_solicitud'";
		//echo $query;
		mysql_query($query,$cotiza);
	}
	else
			$err = 2;
	//header('Location: cotizaciones-ov.php');
}
elseif($acc == "save")
{
	$n_orden     = "OV".substr($id_solicitud,1);
	$cliente     = $_POST['cliente'];
	$embarcar    = $_POST['embarcar'];
	$fecha       = $_POST['fecha'];
	$articulo    = $_POST['articulo'];
	$precio      = $_POST['precio'];
	$costo       = $_POST['costo'];
	$cantidad    = $_POST['cantidad'];
	$um		     = $_POST['um'];
	$nombre      = $_POST['nombre'];
	$moneda		 = $_POST['moneda'];
	$dp			 = $_POST['dolarpeso'];
	$cambio		 = $_POST['tipo_cambio'];
	$facturar    = $_POST['facturar'];
	$flete		 = asigna_numero($_POST['flete']);
	$peso_cono   = number_format($row_cono['result_peso_cono'],5,",",".");
	$um_cono     = "KG";
	$cst_clisse  = asigna_numero($_POST['costo_clisse']);
	$car_cliente = asigna_numero($_POST['cargo_cliente']);
	$car_eroflex = asigna_numero($_POST['cargo_eroflex']);
	$costo       = asigna_numero($_POST['costo']);
	$comision    = asigna_numero($_POST['comision']);
	$margen      = asigna_numero($_POST['margen']);
	$precio      = asigna_numero($_POST['precio']);
	$moneda      = $_POST['moneda'];
	$tipo_cambio = asigna_numero($_POST['tipo_cambio']);
	$c1			 = asigna_numero(str_replace(".","",$_POST['cant_det_1']));
	$c2			 = asigna_numero(str_replace(".","",$_POST['cant_det_2']));
	$c3			 = asigna_numero(str_replace(".","",$_POST['cant_det_3']));
	$c4			 = asigna_numero(str_replace(".","",$_POST['cant_det_4']));
	$c5			 = asigna_numero(str_replace(".","",$_POST['cant_det_5']));
	$c6			 = asigna_numero(str_replace(".","",$_POST['cant_det_6']));
	$c7			 = asigna_numero(str_replace(".","",$_POST['cant_det_7']));
	$c8			 = asigna_numero(str_replace(".","",$_POST['cant_det_8']));
	$c9			 = asigna_numero(str_replace(".","",$_POST['cant_det_9']));
	$c10		 = asigna_numero(str_replace(".","",$_POST['cant_det_10']));
	
	$f1			 = $_POST['fecha_det_1'];
	$f2			 = $_POST['fecha_det_2'];
	$f3			 = $_POST['fecha_det_3'];
	$f4			 = $_POST['fecha_det_4'];
	$f5			 = $_POST['fecha_det_5'];
	$f6			 = $_POST['fecha_det_6'];
	$f7			 = $_POST['fecha_det_7'];
	$f8			 = $_POST['fecha_det_8'];
	$f9			 = $_POST['fecha_det_9'];
	$f10		 = $_POST['fecha_det_10'];
	
	$query = "INSERT INTO result_mstr(result_solic_id, result_costo_clisse, result_cargo_cliente, 
									  result_cargo_eroflex, result_costo, result_margen, result_precio, 
					 				  result_moneda, result_dp, result_tipo_cambio, result_comision, result_flete, result_peso_prod_br)
  				   VALUES ('$id_solicitud', $cst_clisse, $car_cliente, $car_eroflex, $costo, $margen, $precio, 
						   '$moneda', '$dp', $tipo_cambio, $comision, $flete, '$peso_unitario')
  ON DUPLICATE KEY UPDATE 
	                 result_costo_clisse = $cst_clisse, 
				     result_cargo_cliente = $car_cliente, result_cargo_eroflex = $car_eroflex, 
					 result_costo = $costo, result_margen = $margen, result_precio = $precio, 
					 result_moneda = '$moneda', result_dp='$dp', result_tipo_cambio = $tipo_cambio, result_comision = $comision
					 result_flete  = $flete, result_peso_prod_br='$peso_unitario'";

	mysql_query($query,$cotiza) or die(mysql_error());
	
	$query = "INSERT INTO detalle_ov (detalle_solic_id, detalle_c1, detalle_f1, detalle_c2, detalle_f2, detalle_c3,
									  detalle_f3, detalle_c4, detalle_f4, detalle_c5, detalle_f5, detalle_c6,
									  detalle_f6, detalle_c7, detalle_f7, detalle_c8, detalle_f8, detalle_c9,
									  detalle_f9, detalle_c10, detalle_f10)
							  VALUES ('$id_solicitud',$c1,'$f1',$c2,'$f2',$c3,'$f3',$c4,'$f4',$c5,'$f5',$c6,'$f6',
									  $c7,'$f7',$c8,'$f8',$c9,'$f9',$c10,'$f10')
			 ON DUPLICATE KEY UPDATE  detalle_c1 = $c1, detalle_f1 = '$f1', detalle_c2 = $c2, detalle_f2 = '$f2',
			                          detalle_c3 = $c3, detalle_f3 = '$f3', detalle_c4 = $c4, detalle_f4 = '$f3',
									  detalle_c5 = $c5, detalle_f5 = '$f5', detalle_c6 = $c6, detalle_f6 = '$f6', 
									  detalle_c7 = $c7, detalle_f7 = '$f7', detalle_c8 = $c8, detalle_f8 = '$f8',
									  detalle_c9 = $c9, detalle_f9 = '$f9', detalle_c10= $c10,detalle_f10= '$f10'";
	mysql_query($query,$cotiza) or die (mysql_error());

	//se actualiza la fecha de creacion de la OV
	$fecha2 = date("Y-m-d");
	
	if($facturar != "")
		$query = "UPDATE solic_mstr SET solic_fecha_ov = '$fecha2', solic_um = '$facturar', 
		                 solic_cantidad = $cantidad
		           WHERE solic_id = '$id_solicitud'";
	else
		$query = "UPDATE solic_mstr SET solic_fecha_ov = '$fecha2', solic_cantidad = $cantidad 
		           WHERE solic_id = '$id_solicitud'";
				   
	mysql_query($query,$cotiza);
	$origen = "save";
}



$query    = "SELECT * FROM result_mstr WHERE result_solic_id = '$id_solicitud'";
$rslt_res = mysql_query($query,$cotiza);
$row_resu = mysql_fetch_assoc($rslt_res);

$query    = "SELECT * FROM detalle_ov WHERE detalle_solic_id = '$id_solicitud'";
$rslt_det = mysql_query($query,$cotiza);
$row_res  = mysql_fetch_assoc($rslt_det);

$tot_detalle = $row_res['detalle_c1'] + $row_res['detalle_c2'] + $row_res['detalle_c3'] + $row_res['detalle_c4'] + $row_res['detalle_c5'] + $row_res['detalle_c6'] + $row_res['detalle_c7'] + $row_res['detalle_c8'] + $row_res['detalle_c9'] + $row_res['detalle_c10'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="../lib/jQuery/sexylightbox.css" type="text/css" media="all" />
<title>...::: Generaci&oacute;n de OV :::...</title>
<!--SexyLightbox-->
<script language="javascript" type="text/javascript" src="../lib/jQuery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../lib/jQuery/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="../lib/jQuery/sexylightbox.v2.3.jquery.min.js"></script>
<script type="text/javascript" src="../lib/jQuery/sexyalertbox.v1.2.jquery.js"></script>
<!--SexyLightbox-->

<!--Calendario-->
<script language="JavaScript" src="../lib/calendar/calendar_us.js"></script>
<script type="text/javascript" src="../lib/libreriaAjax.js"></script>
<link rel="stylesheet" href="../lib/calendar/calendar.css">
<!--Calendario-->

<link href="../../main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../lib/funciones.js"></script>
<script language="javascript">
<?php if($err == 2){ ?>
	alert("Ha ocurrido un error desconocido mientras se generaba la Orden de venta.\nPor favor comunique este mensaje al administrador del sistema.");
//	window.open('');
<?php }?>
<?php if($err == 1){ ?>
	alert("<?php echo $msg;?>");
	//window.open('cotizaciones-ov.php','_self');
<?php }?>
<?php if($err == -1){ ?>
	confirm("<?php echo $msg;?>");
	window.open('../rep/esperaov.php','_self');
<?php }?>
<?php if($origen == "save"){ ?>
	if(confirm("La cotizaciï¿½n ha sido guardada exitosamente.\nï¿½Desea revisarla?"))
		window.open('../rep/repov.php?id_s=<?php echo $id_solicitud;?>','_self');
//	window.open('');
<?php }?>
function guardar_cotizacion()
{
	document.forms.form1.action= "genera_ov2.php?acc=save&id_solicitud=<?php echo $id_solicitud;?>";
	document.form1.submit();
}

function generar_ov()
{
	var moneda		= document.getElementById("moneda").value;
	var dolarpeso	= document.getElementById("dolarpeso").value;
	var peso_unitario = document.getElementById("peso_unitario").value;
	
	if (peso_unitario=='' || peso_unitario=='0')
	{
		alert("Error en los datos de la solicitud\nNo se ha definido un peso unitario válido.");
		return;
	}
	
	
	if(moneda == "USD" && dolarpeso == "SO")
	{
		alert("Error en los datos de la solicitud\nSe ha seleccionado como moneda USD. Para esta moneda es obligatorio indicar si aplica o no como D-P");
		return;
	}
	else
	{
		if(confirm('Si decide aceptar, la Orden de Venta se generarï¿½ automï¿½ticamente en QAD.\nï¿½Desea continuar?'))
		{
			document.forms.form1.action= "genera_ov2.php?acc=generar&id_solicitud=<?php echo $id_solicitud;?>";
			document.form1.submit();
		}
		else
		{
			if(confirm('Ha decidido no generar la orden de ventas en QAD.ï¿½Desea guardar sus cambios?'))	
			{
				document.forms.form1.action= "genera_ov2.php?acc=save&id_solicitud=<?php echo $id_solicitud;?>";
				document.form1.submit();
			}
		}
	}
}




/*function calcula(costo)
{
	var margen = window.document.getElementById('margen').value;
	window.document.getElementById('precio').value = Math.round(costo / (1 - (margen/100)));
}



function calcula_precio(margen)
{
	var costo = window.document.getElementById('costo').value;
	window.document.getElementById('precio').value = Math.round(costo / (1 - (margen/100)));
}



function calcula_margen(precio)
{
	var costo = window.document.getElementById('costo').value;
	//window.document.getElementById('margen').value = 100 * precio / costo - 100;
	window.document.getElementById('margen').value = precio / costo - 100;
}
*/



function calcula(objCosto)
{
	var costo = objCosto.value;
	var margen = (window.document.getElementById('margen').value) / 100;
	var precio = costo / (1 - margen);

	//alert (precio);
	
	window.document.getElementById('precio').value = precio;
	ajustaDecimales(window.document.getElementById('precio'), getTotalDecimales(objCosto));
	
}




function calcula_margen(objPrecio)
{
	
	//ajustaDecimales(window.document.getElementById('costo'), getTotalDecimales(window.document.getElementById('precio')));
	
	var precio = objPrecio.value;
	var costo = window.document.getElementById('costo').value;
	window.document.getElementById('margen').value = Math.round(100 * (1 - costo / precio),4);
}





function calcula_precio(objPct)
{
	var pct = objPct.value;
	var costo = window.document.getElementById('costo').value;
	var margen = pct / 100;
	
	var precio = costo / (1 - margen);
	
	window.document.getElementById('precio').value = precio;
	ajustaDecimales(window.document.getElementById('precio'), getTotalDecimales(window.document.getElementById('costo')));
	
	
	
	
}





function getTotalDecimales(obj)
{
	var ValorOriginal = obj.value;
	var auxValor = ValorOriginal.split('.');
	var ValorFinal = "";
	
	if (auxValor.length == 1)
	{
		return 0;
	}
	else 
	{
		var aux = auxValor[1];
		return aux.length;
	}
}






function ajustaDecimales(obj, tot_dec)
{
	var ValorOriginal = obj.value;
	var auxValor = ValorOriginal.split('.');
	var ValorFinal = "";
	
	ValorFinal = auxValor[0];
	
	//alert(ValorFinal);
	
	if (auxValor.length == 1)
	{
		ValorFinal = auxValor[0];
		
		if (tot_dec>0)
		{
			ValorFinal = ValorFinal + ".";
		}
		
		for (var i=0; i<tot_dec; i++)
		{
			ValorFinal = ValorFinal + "0";
		}
	}
	else 
	{
		var aux = auxValor[1];
		
		if (aux.length < tot_dec)
		{
			ValorFinal = auxValor[0] + "." + aux;
			
			for (var i=aux.length; i<tot_dec; i++)
			{
				ValorFinal = ValorFinal + "0";
			}
		}
		else if (aux.length == tot_dec)
		{
			ValorFinal = auxValor[0] + "." +  aux;
		}
		else
		{
			ValorFinal = auxValor[0] + "." +  aux.substring(0,tot_dec);
		}
	}
	
	obj.value = ValorFinal;
}




function suma()
{
	var total = <?php echo $row['solic_cantidad'];?>;
	var cnt1  = parseInt(window.document.getElementById('cant_det_1').value.replace(".",""));
	var cnt2  = parseInt(window.document.getElementById('cant_det_2').value.replace(".",""));
	var cnt3  = parseInt(window.document.getElementById('cant_det_3').value.replace(".",""));
	var cnt4  = parseInt(window.document.getElementById('cant_det_4').value.replace(".",""));
	var cnt5  = parseInt(window.document.getElementById('cant_det_5').value.replace(".",""));
	var cnt6  = parseInt(window.document.getElementById('cant_det_6').value.replace(".",""));
	var cnt7  = parseInt(window.document.getElementById('cant_det_7').value.replace(".",""));
	var cnt8  = parseInt(window.document.getElementById('cant_det_8').value.replace(".",""));
	var cnt9  = parseInt(window.document.getElementById('cant_det_9').value.replace(".",""));
	var cnt10 = parseInt(window.document.getElementById('cant_det_10').value.replace(".",""));
	
	if(isNaN(cnt1))
		cnt1 = 0;
	if(isNaN(cnt2))
		cnt2 = 0;
	if(isNaN(cnt3))
		cnt3 = 0;
	if(isNaN(cnt4))
		cnt4 = 0;
	if(isNaN(cnt5))
		cnt5 = 0;
	if(isNaN(cnt6))
		cnt6 = 0;
	if(isNaN(cnt7))
		cnt7 = 0;
	if(isNaN(cnt8))
		cnt8 = 0;
	if(isNaN(cnt9))
		cnt9 = 0;
	if(isNaN(cnt10))
		cnt10 = 0;
		
	var suma = cnt1 + cnt2 + cnt3 + cnt4 + cnt5 + cnt6 + cnt7 + cnt8 + cnt9 + cnt10;
	
	if(suma == total)
		window.document.getElementById('generar').disabled = false;
	else
		window.document.getElementById('generar').disabled = true;
		
	window.document.getElementById('total-suma').innerHTML = suma;
}

function devolver(sol)
{
	SexyLightbox.initialize({color:'black', dir: '../lib/jQuery/sexyimages'});
   	SexyLightbox.display('../auxiliar/devolver_gte.php?origen=ov2&id_solicitud=' + sol + '&TB_iframe=true&height=200&width=500');
	
}




function ValidaAplicaDP(moneda)
{
	if (moneda != "USD")
	{
		document.getElementById("dolarpeso").disabled = true;
	}
	else
	{
		document.getElementById("dolarpeso").disabled = false;
	}
	
}	//	function ValidaAplicaDP(moneda)




</script>
</head>

<body>
<form id="form1" name="form1" method="post" action="genera_ov.php?acc=generar&id_solicitud=<?php echo $id_solicitud;?>">
  <strong>Datos de Solicitud</strong>
<table border="0">
    <tr>
      <td width="89">Solicitud</td>
      <td width="4">:</td>
      <td width="270"><?php echo $row['solic_id'];?></td>
      <td width="165"><input type="hidden" name="cliente" id="cliente" value="<?php echo $row['cm_addr']?>" /></td>
    </tr>
    <tr>
      <td width="89">Cliente</td>
      <td width="4">:</td>
      <td width="270"><?php echo $row['cm_rut']." ".$row['cm_sort'];?></td>
      <td width="165"><input type="hidden" name="cliente" id="cliente" value="<?php echo $row['cm_addr']?>" /></td>
    </tr>
    <tr>
      <td>Fecha Solicitada</td>
      <td>:</td>
      <td><?php echo $row['solic_fecha_solic']?></td>
      <td><input type="hidden" name="fecha" id="fecha" value="<?php echo $row['solic_fecha_solic']?>" /></td>
    </tr>
    </table>
    <br />
    <strong>Datos de Cliente </strong><br />
<table border="0">
      <tr>
        <td width="87">Rut</td>
        <td width="4">:</td>
        <td width="263"><?php echo $row['cm_rut'];?></td>
        <td width="55">&nbsp;</td>
        <td width="4">&nbsp;</td>
        <td width="150">&nbsp;</td>
      </tr>
      <tr>
        <td width="87">Raz&oacute;n social</td>
        <td width="4">:</td>
        <td width="263"><?php echo $row['cm_sort'];?></td>
        <td width="57">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Giro</td>
        <td>:</td>
        <td><?php echo $row['cm_giro'];?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Direcci&oacute;n</td>
        <td>:</td>
        <td><?php echo $row['cm_direccion']?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Pa&iacute;s</td>
        <td>:</td>
        <td><?php echo $row['cm_pais']?></td>
        <td>Regi&oacute;n</td>
        <td>:</td>
        <td><?php echo $row['cm_region']?></td>
      </tr>
      <tr>
        <td>Ciudad</td>
        <td>:</td>
        <td><?php echo $row['cm_ciudad']?></td>
        <td>Comuna</td>
        <td>:</td>
        <td><?php echo $row['cm_comuna']?></td>
      </tr>
    </table>
  <br />
  <strong>Datos Despacho</strong><br />
  <table border="0">
    <tr>
      <td width="88">Embarcar A</td>
      <td width="4">:</td>
      <td width="263"><?php echo $row['solic_ddespacho'];?></td>
      <td width="55"><input type="hidden" name="embarcar" id="embarcar" value="<?php echo $row['solic_ddespacho']?>" /></td>
      <td>&nbsp;</td>
      <td width="130">&nbsp;</td>
    </tr>
    <tr>
      <td width="88">Pa&iacute;s</td>
      <td width="4">:</td>
      <td width="263"><?php echo $row['ad_pais'];?></td>
      <td>Regi&oacute;n</td>
      <td>:</td>
      <td width="130"><?php echo $row['ad_region']?></td>
    </tr>
    <tr>
      <td>Ciudad</td>
      <td>:</td>
      <td><?php echo $row['ad_ciudad'];?></td>
      <td>Comuna</td>
      <td>:</td>
      <td><?php echo $row['ad_comuna'];?></td>
    </tr>
  </table>
<br />
<strong>Datos Art&iacute;culo</strong><br />
  <table border="0">
    <tr>
      <td width="88">Nombre</td>
      <td width="4">:</td>
      <td width="263"><input name="nombre" type="text" class="default" id="nombre" value="<?php echo $row['solic_npcliente'];?>" size="48" maxlength="48" <?php if($tipo == 2) echo "readonly=\"readonly\"";?> /></td>
    </tr>
    <tr>
      <td width="88">Tipo de producto</td>
      <td width="4">:</td>
      <td width="263"><?php echo $row['tipo_desc'];?></td>
    </tr>
    <?php if($tipo_biolsa != "" && $tipo_biolsa != 0){?>
    <tr>
      <td width="88">Tipo de bolsa</td>
      <td width="4">:</td>
      <td width="263"><?php echo $tipo_biolsa;?></td>
    </tr>
    <?php }//if($tipo_biolsa != "" && $tipo_biolsa != 0)?>
    <tr>
      <td>Material</td>
      <td>:</td>
      <td><?php echo $row['solic_material'];?></td>
    </tr>
    <tr>
      <td>Ancho</td>
      <td>:</td>
      <td><?php echo $row['solic_ancho'];?></td>
    </tr>
    <?php if($row['solic_largo'] != "" && $row['solic_largo']!= 0){?>
    <tr>
      <td>Largo</td>
      <td>:</td>
      <td><?php echo $row['solic_largo'];?></td>
    </tr>
    <?php }//if($row['solic_largo'] != "" && $row['solic_largo']!= 0)?>
    <?php if($paso_taca != "" && $paso_taca != 0){?>
    <tr>
      <td>Paso Taca</td>
      <td>:</td>
      <td><?php echo $paso_taca;?></td>
    </tr>
    <?php }//if($paso_taca != "" && $paso_taca != 0)?>
    <tr>
      <td>Espesor <?php echo $row['solic_material1'];?></td>
      <td>:</td>
      <td><?php echo $row['solic_espesor1'];?></td>
    </tr>
    <?php if($row['solic_espesor2'] != "" && $row['solic_espesor2'] != 0){?>
    <tr>
      <td>Espesor <?php echo $row['solic_material2'];?></td>
      <td>:</td>
      <td><?php echo $row['solic_espesor2'];?></td>
    </tr>
    <?php }//if($row['solic_espesor2'] != "" && $row['solic_espesor2'] != 0)?>
    <?php if($row['solic_espesor3'] != "" && $row['solic_espesor3'] != 0){?>
    <tr>
      <td>Espesor <?php echo $row['solic_material3'];?></td>
      <td>:</td>
      <td><?php echo $row['solic_espesor3'];?></td>
    </tr>
    <?php }//if($row['solic_espesor3'] != "" && $row['solic_espesor3'] != 0)?>
    
    
    <?php if($row['solic_um'] == "UN"){?>
    <tr>
      <td>Peso Unitario</td>
      <td>:</td>
      <td><input name="peso_unitario" type="text" class="default" id="peso_unitario" value="<?php echo $row_resu['result_peso_prod_br'];?>" autocomplete="off" onkeypress="return valida_numeros(event)" />&nbsp;&nbsp;gramos</td>
    </tr>
    <?php 
	}
	else
	{
		?>
        <input type="hidden" name="peso_unitario" id="peso_unitario" value="<?php if ($row_resu['result_peso_prod_br']!="") { echo $row_resu['result_peso_prod_br']; } else { echo $row['solic_cantidad']; }?>" />
        <?php
	}//if($row['solic_espesor3'] != "" && $row['solic_espesor3'] != 0)?>
    
    
  </table>
  <br />
  <strong>Datos OV</strong><br />
    <table border="0">
    <tr>
      <td>C&oacute;digo Art&iacute;culo</td>
      <td>:</td>
      <td><?php echo $row['solic_serie'].$version;?></td>
      <td><input type="hidden" name="articulo" id="articulo" value="<?php echo $row['solic_serie'].$version;?>" /></td>
    </tr>
    <tr>
      <td>O. de Compra</td>
      <td>:</td>
      <td><?php echo $row['solic_oc'];?></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Costo real Clisse</td>
      <td>:</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Costo Clisse Solic</td>
      <td>:</td>
      <td><input name="costo_clisse" type="text" class="default" id="costo_clisse" value="<?php echo $row_resu['result_costo_clisse'];?>" autocomplete="off"/></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Cargo Cliente</td>
      <td>:</td>
      <td><input name="cargo_cliente" type="text" class="default" id="cargo_cliente" value="<?php echo $row_resu['result_cargo_cliente'];?>" autocomplete="off"/></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Cargo Eroflex</td>
      <td>:</td>
      <td><input name="cargo_eroflex" type="text" class="default" id="cargo_eroflex" value="<?php echo $row_resu['result_cargo_eroflex'];?>" autocomplete="off"/></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Compromiso</td>
      <td>:</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Flete</td>
      <td>:</td>
      <td><input name="flete" type="text" class="default" id="flete" onkeypress="return valida_numeros(event)"  autocomplete="off" value="<?php echo $row_resu['result_flete'];?>" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Costo</td>
      <td>:</td>
      <td><input name="costo" type="text" class="default" id="costo" onkeypress="return valida_numeros(event)" onkeyup="calcula(this)" onblur="ajustaDecimales(this, getTotalDecimales(this))" autocomplete="off" value="<?php echo $row_resu['result_costo'];?>" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Margen</td>
      <td>:</td>
      <td><input name="margen" type="text" class="default" id="margen" value="<?php if($row_resu['result_margen'] == "")echo 10; else echo $row_resu['result_margen'];?>" size="5" onkeypress="return valida_numeros(event)" onkeyup="calcula_precio(this)" autocomplete="off" />
        %</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Precio</td>
      <td>:</td>
      <td><input name="precio" type="text" class="default" id="precio" onkeypress="return valida_numeros(event)" onkeyup="calcula_margen(this)" onblur="ajustaDecimales(window.document.getElementById('precio'), getTotalDecimales(window.document.getElementById('costo')));" value="<?php echo $row_resu['result_precio'];?>" autocomplete="off"/></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Comisi&oacute;n</td>
      <td>:</td>
      <td><input name="comision" type="text" class="default" id="comision" onkeypress="return valida_numeros(event)" value="<?php echo $row_resu['result_comision'];?>" size="5" autocomplete="off"/>
        %</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Cantidad</td>
      <td>:</td>
      <td><input name="cantidad" type="text" class="default" id="cantidad" value="<?php echo $row['solic_cantidad']?>" autocomplete="off"/></td>
      <td>&nbsp;</td>
    </tr>
    <?php if($row['solic_um'] == "KG" || $row['solic_um'] == "KGN"){?>
    <tr>
      <td>Facturar</td>
      <td>:</td>
      <td><select name="facturar" class="default" id="facturar">
        <option value="KG" <?php if($row['solic_um'] == "KG") echo "selected=\"selected\""; ?>>Kilogramos Brutos</option>
        <option value="KN" <?php if($row['solic_um'] == "KN") echo "selected=\"selected\""; ?>>Kilogramos Netos</option>
      </select></td>
      <td>&nbsp;</td>
    </tr>
    <?php }?>
    <tr>
      <td>Moneda</td>
      <td>:</td>
      <td><select name="moneda" class="default" id="moneda" onchange="ValidaAplicaDP(this.value);">
        <?php 
			$query  = "SELECT * FROM moneda_mstr";
			$rslt_m = mysql_query($query,$cotiza);
			$row_m  = mysql_fetch_assoc($rslt_m);
			$curr		= $row_resu['result_moneda'];
			$dp			= $row_resu['result_dp'];
			/*
			do{
		?>
        <option value="<?php echo $row_m['moneda_id'];?>" <?php if($row_resu['result_moneda'] == $row_m['moneda_id'])echo "selected=\"selected\"";?>><?php echo $row_m['moneda_desc'];?></option>
        <?php }while($row_m  = mysql_fetch_assoc($rslt_m)); */?>
        
        <option value="CLP" <?php if($row_resu['result_moneda'] == 'CLP')echo "selected=\"selected\"";?>> CLP : Peso Chile </option>
            <option value="USD" <?php if($row_resu['result_moneda'] == 'USD')echo "selected=\"selected\"";?>> USD : D&oacute;lar</option>
            <option value="USR" <?php if($row_resu['result_moneda'] == 'USR')echo "selected=\"selected\"";?>> USR  : D&oacute;lar Relac.</option>
            
      </select></td>
      <td><input type="hidden" name="hiddenField7" id="hiddenField7" /></td>
    </tr>
    <tr>
    	<td>Es D&oacute;lar/Peso</td>
    <td>:</td>
    <td>
        <select 
        	name	="dolarpeso" 
            class	="default" 
            id		="dolarpeso" 
			<?php if($curr != 'USD') { echo ' disabled="disabled"'; } ?> 
       	>
        	<option value="SO" <?php if($dp == 'SO' || $dp == '' )echo "selected=\"selected\"";?> >Seleccione Opci&oacute;n</option>
            <option value="SI" <?php if($dp == 'SI')echo "selected=\"selected\"";?> >SI</option>
            <option value="NO" <?php if($dp == 'NO')echo "selected=\"selected\"";?> >NO</option>
        </select>
        
    </td>
    <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Unidad de medida</td>
      <td>:</td>
      <td><?php if($row['solic_um'] == "UN") 
	  					echo "Unidades";
	            elseif ($row['solic_um'] == "KG") 
					echo "Kilogramos";
				elseif ($row['solic_um'] == "KN") 
					echo "Kilogramos Netos";
			?>
                </td>
      <td><input type="hidden" name="um" id="um" value="<?php echo $row['solic_um']; ?>" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td valign="top"><strong>Detalle</strong></td>
      <td>&nbsp;</td>
      <td><table border="0">
        <tr>
          <td width="21">Ln</td>
          <td width="80">Cantidad</td>
          <td width="110">Fecha</td>
        </tr>
        <tr>
          <td align="right">1.-</td>
          <td><input name="cant_det_1" type="text" class="default" id="cant_det_1" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c1']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_1" type="text" class="default" id="fecha_det_1" size="10" readonly="readonly" value="<?php echo $row_res['detalle_f1']?>"/>
          <script language="JavaScript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_1'});
	      </script>
          </td>
        </tr>
        <tr>
          <td align="right">2.-</td>
          <td><input name="cant_det_2" type="text" class="default" id="cant_det_2" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c2']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_2" type="text" class="default" id="fecha_det_2" size="10" value="<?php echo $row_res['detalle_f2']?>" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_2'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">3.-</td>
          <td><input name="cant_det_3" type="text" class="default" id="cant_det_3" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c3']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_3" type="text" class="default" value="<?php echo $row_res['detalle_f3']?>" id="fecha_det_3" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_3'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">4.-</td>
          <td><input name="cant_det_4" type="text" class="default" id="cant_det_4" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c4']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_4" type="text" class="default" value="<?php echo $row_res['detalle_f4']?>" id="fecha_det_4" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_4'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">5.-</td>
          <td><input name="cant_det_5" type="text" class="default" id="cant_det_5" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c5']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_5" type="text" class="default" value="<?php echo $row_res['detalle_f5']?>" id="fecha_det_5" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_5'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">6.-</td>
          <td><input name="cant_det_6" type="text" class="default" id="cant_det_6" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c6']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_6" type="text" class="default" value="<?php echo $row_res['detalle_f6']?>" id="fecha_det_6" size="10" readonly="readonly"/>            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_6'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">7.-</td>
          <td><input name="cant_det_7" type="text" class="default" id="cant_det_7" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c7']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_7" type="text" class="default" value="<?php echo $row_res['detalle_f7']?>" id="fecha_det_7" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_7'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">8.-</td>
          <td><input name="cant_det_8" type="text" class="default" id="cant_det_8" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c8']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_8" type="text" class="default" value="<?php echo $row_res['detalle_f8']?>" id="fecha_det_8" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_8'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">9.-</td>
          <td><input name="cant_det_9" type="text" class="default" id="cant_det_9" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c9']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_9" type="text" class="default" value="<?php echo $row_res['detalle_f9']?>" id="fecha_det_9" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_9'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">10.-</td>
          <td><input name="cant_det_10" type="text" class="default" id="cant_det_10" size="10" onkeyup="puntos(this,this.value.charAt(this.value.length-1));suma()" value="<?php echo $row_res['detalle_c10']?>" autocomplete="off"/></td>
          <td><input name="fecha_det_10" type="text" class="default" value="<?php echo $row_res['detalle_f10']?>" id="fecha_det_10" size="10" readonly="readonly"/>
            <script language="JavaScript" type="text/javascript">
		  	new tcal ({'formname': 'form1','controlname': 'fecha_det_10'});
	        </script></td>
        </tr>
        <tr>
          <td align="right">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="right">&nbsp;</td>
          <td><div id="total-suma"><?php echo $tot_detalle;?></div></td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><input name="button" type="button" class="default" id="button" value="Devolver" onclick="devolver('<?php echo $id_solicitud;?>');" />
      <input name="button2" type="button" class="default" id="button2" value="Volver" onclick="window.open('../rep/esperaov.php','_self')" />        <input name="generar" <?php if($tot_detalle != $row['solic_cantidad']) echo "disabled=\"disabled\""; ?> type="button" onclick="generar_ov()" class="default" id="generar" value="Generar OV" /></td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
<?php 
mysql_close($cotiza);
?>