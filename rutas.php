<?php 
if(isset($_REQUEST["marka"])){
	$dbconn = pg_pconnect("host=localhost port=5432 dbname=dalota user=postgres password=DALota") or die ("Could not connect");
	
	$marka = $_REQUEST["marka"];
	$markb = $_REQUEST["markb"];
	$puntoa = $_REQUEST["puntoa"];
	$puntob = $_REQUEST["puntob"];
	
	list($lat1,$lng1,$lat2,$lng2) = explode(",",$marka);
	$marka = "ST_MakeEnvelope($lng1,$lat1,$lng2,$lat2, -1)";
	list($lat1,$lng1,$lat2,$lng2) = explode(",",$markb);
	$markb = "ST_MakeEnvelope($lng1,$lat1,$lng2,$lat2, -1)";

	$sql = "SELECT DISTINCT ON (ruta_name) ruta_name, operador,ruta_name2, distrito_a, distrito_b, alias, AsText(ST_Line_Interpolate_Point(kml,ST_Line_Locate_Point(kml,ST_GeomFromText('POINT($puntoa)',-1)))) as puntoa,
	 AsText(ST_Line_Interpolate_Point(kml,ST_Line_Locate_Point(kml,ST_GeomFromText('POINT($puntob)',-1)))) as puntob, astext(kml) as rutas  FROM dal_recorridos where st_intersects($marka,kml) and st_intersects($markb,kml)";

$result = pg_query($dbconn, $sql);

if ($result) {
	$result = pg_fetch_all($result);
	$json = Array();
	$linea = 0;
	foreach ($result as $line) {
		$json2 = Array();
        $wkb = substr($line["rutas"],11,-1);
		$lineas = explode(",",$wkb);
		foreach($lineas as $linea){
			list($latitude,$longitude) = explode(" ",$linea);
			$json2[] = Array( (float) $latitude, (float) $longitude);
		}
		$json[] = Array("ruta" => $json2);
		
		//Obtener paraderos interseccion cercana.
		$wkb = substr($line["puntoa"],6,-1);
		list($latitude,$longitude) = explode(" ",$wkb);
		$bus_a = Array( (float) $latitude, (float) $longitude);
		
		$wkb = substr($line["puntob"],6,-1);
		list($latitude,$longitude) = explode(" ",$wkb);
		$bus_b = Array( (float) $latitude, (float) $longitude);

		$datos[] = Array(
			"ruta_name" => $line["ruta_name"],
			"imagen" => $line["ruta_name2"],
			"operador" => $line["operador"],
			"distrito_a" => $line["distrito_a"],
			"distrito_b" => $line["distrito_b"],
			"alias" => $line["alias"],
			"puntoa" => $bus_a,
			"puntob" => $bus_b
			);
    }

/*		#TODO: Mostrar la la ruta hasta la interseccion.
		$xpuntoa = explode(" ",$puntoa);
		$url = "http://maps.googleapis.com/maps/api/directions/json?origin=".$xpuntoa[1].",".$xpuntoa[0]."&destination=".$bus_a[1].",".$bus_a[0]."&sensor=true&mode=walking";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
		$datajson = curl_exec($ch);
		curl_close($ch);
		var_dump($datajson);
		//$datajson = $datajson["routes"][0].["legs"];
		var_dump($datajson);
*/

	$data = array("rutas" => $json, "meta" => $datos);	
	echo json_encode($data);
} else {
	echo '{{"error":"No hay rutas disponibles cerca."}}';
}
}
	?>