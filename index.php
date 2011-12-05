<!DOCTYPE html>
<html>
<head>
<title>Quiero Ir !</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<link rel="stylesheet" type="text/css" href="css/jquery.mobile-1.0.min.css">
<link rel="stylesheet" href="themes/ghei.css" />
<style type="text/css">
	html { height: 100% }
	body { height: 100%; margin: 0; padding: 0 }
	#map_canvas { height: 100% }
	#textfloat{ position: absolute;z-index: 99999;max-width: 200px;background: white;top: 60px;left: 80px;padding: 4px 4px 4px 10px;width: 40%;font-size: 12px;-webkit-border-radius:5px}
	#textfloat img { float:left;margin-rigth:5px; }
	
	.nav-rdn .ui-btn .ui-btn-inner { padding-top: 40px !important; }
	.nav-rdn .ui-btn .ui-icon { width: 30px!important; height: 30px!important; margin-left: -15px !important; box-shadow: none!important; -moz-box-shadow: none!important; -webkit-box-shadow: none!important; -webkit-border-radius: 0 !important; border-radius: 0 !important; }
	#marker .ui-icon { background:  url(images/marker.png) 50% 50% no-repeat; background-size: 20px 34px; }
	#search .ui-icon { background:  url(images/search.png) 50% 50% no-repeat; background-size: 30px 34px;  }
	#wanna .ui-icon { background:  url(images/logOTA.png) 50% 50% no-repeat; background-size: 30px 34px;  }
</style>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC_f3rnHWrn_Is07mYS-3Zj1PaHDk4hfMs&sensor=true"></script>
<script type="text/javascript" src="js2/jquery.min.js"></script>
<script src="js2/jquery-ui.min.js" type="text/javascript"></script>
<script src="js2/jquery.ui.map.js" type="text/javascript"></script>
<script src="js2/jquery.mobile-1.0.min.js" type='text/javascript'></script>
<script type="text/javascript">
var map,latlng,latlng2;
var markersArray = [];
$(window).load(function(){
	setTimeout(scrollTo,200,0,1);
    $(function() {
	//Marker Inicio y Fin
	latlng = new google.maps.LatLng(-12.0633, -77.0365);
	latlng2 = new google.maps.LatLng(-11.991299,-77.125634);

	getCurrentPosition = function(callback) {
		if(navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            function(pos) {
              latlng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                callback(latlng);
              }, callback(latlng));
        }
        else if (google.gears) { // A ser deprecado.. dic2011
			var geo = google.gears.factory.create('beta.geolocation');
			geo.getCurrentPosition(
				function(pos) {
					latlng = new google.maps.LatLng(pos.latitude,pos.longitude);
					callback(latlng);
				}, callback(latlng));          
        }	// Y si no va nada...
        else {
          callback(latlng); //Definida antes.
        }
      };
    getCurrentPosition(initialize);
    });

function initialize() {
    var myOptions = {
		zoom: 11,
		center: latlng,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);

	var markerA = new google.maps.Marker({
		position: latlng,
		map: map,
		title:"Inicio",
		draggable: true,
		zIndex:100,
		icon :  "images/mainroad.png"
	});
	var circleA = new google.maps.Circle({
		map: map,
        radius: 1300,
		zIndex: 99,
		strokeColor: "#550000",
		strokeOpacity: 0.5,
		strokeWeight: 2,
	});
    circleA.bindTo('center', markerA, 'position');
 
	var markerB = new google.maps.Marker({
		position: latlng2,
		map: map,
		title:"Fin",
		draggable: true,
		zIndex:100,
		icon :  "images/stop.png"
	});
	var circleB = new google.maps.Circle({
        map: map,
        radius: 1300,
		zIndex: 99,
		strokeColor: "#550000",
		strokeOpacity: 0.5,
		strokeWeight: 2,
	});
    circleB.bindTo('center', markerB, 'position');
	
	function routes(){
	var request = $.getJSON("rutas.php",{
		marka: circleA.getBounds().toUrlValue(5),
		markb: circleB.getBounds().toUrlValue(5),
		puntoa: markerA.getPosition().lng()+" "+markerA.getPosition().lat(),
		puntob: markerB.getPosition().lng()+" "+markerB.getPosition().lat()
	}, function (results) {
		createStringline(results);
		var puntoageo = new google.maps.LatLng(results.meta[0].puntoa[1], results.meta[0].puntoa[0]);
		var busA = new google.maps.Marker({
			position: puntoageo,
			map: map,
			draggable: false,
			zIndex:100,
			icon :  "images/busstop.png"
		});
		var puntobgeo = new google.maps.LatLng(results.meta[0].puntob[1], results.meta[0].puntob[0]);
		var busB = new google.maps.Marker({
			position: puntobgeo,
			map: map,
			draggable: false,
			zIndex:100,
			icon :  "images/busstop.png"
		});
	markersArray.push(busA);
	markersArray.push(busB);
	var car = results.meta[0];
	content = "<h3>"+car.ruta_name+" - "+car.operador+"</h3><img src=\"vehiculos/"+car.imagen+".jpg\" width=\"90%\" heigth=\"auto\"/><p><strong>Distrito Inicial</strong>: "+car.distrito_a+"<br/><strong>Distrito Final</strong>: "+car.distrito_b+"</p>";
	$("#textfloat").html(content);
	}, function (err){
		alert(err.error);
	});
}
	
	google.maps.event.addListener(markerB, 'dragend', function() {
		routes();
	});
	google.maps.event.addListener(markerA, 'dragend', function() {
		routes();
	});
}

function createStringline(geojson) {
	clearOverlays();
	geojson = geojson.rutas;
	var colores = ["#FF0000", "#000000", "#AA00AA", "#DDEECC"];
    $.each(geojson, function (i, n) {
		var json = n.ruta;
        var pathx = [];
        $.each(json, function (j, p) {
            var ll = new google.maps.LatLng(p[1], p[0]);
            pathx.push(ll);
        });
		var ruta = new google.maps.Polyline({
			path: pathx,
			strokeColor: colores[i],
			strokeOpacity: 0.5,
			strokeWeight: 2,
			map: map
		});
		ruta.setMap(map);
		markersArray.push(ruta);
    });
}

function clearOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
  }
}

function createInfoWindow(ruta,content,mapa) { //no se usa aun
    google.maps.event.addListener(ruta, 'click', function(x) {
        var infowindow = new google.maps.InfoWindow(
      { content: content,
        position : x.latLng,
      });
        infowindow.open(mapa,ruta);
    });
}
});
</script>
</head>
<body >
<!---- GOOGLE MAPS PAGE ----->
<div data-role="page" id="maps">
<div id="textfloat"><p><img src="images/mainroad.png" alt="Fin"/>El lugar donde se encuentra esta indicado por este simbolo.</p><p><img src="images/stop.png" alt="Fin"/>Para que se muestre las rutas cercanas a su destino use el icono de <strong>stop</strong>.</p></div>
    <div data-role="header" data-position="fixed">
		<h1>Quiero Ir!</h1>
    </div><!-- /header -->
     <!-- <div data-role="content"> -->
        <div id="map_canvas" style="min-height:690px;  width:100%"></div>
   <!-- </div> /content -->
<div data-role="footer"  class="nav-rdn" data-position="fixed">
    <div data-role="navbar" class="nav-rdn" data-grid="d" >
		<ul>
			<li><a href="search.html" id="marker" data-icon="custom" data-rel="dialog">Rutas</a></li>
			<li><a href="add.html" id="search" data-icon="custom" data-rel="dialog" data-transition="slidedown">Por avenidas</a></li>
			<li><a href="http://ota-peru.com" id="wanna" data-icon="custom" target="_blank" >Powered by Frikis</a></li>
		</ul>
		</div>
    </div><!-- /footer -->
</div><!-- /page -->

</body>
</html>