<?php
include('settings_t.php');
$lat=40.6701;
$lon=16.5999;

	//$lon=$row[0]['lng'];
	//$lat=$row[0]['lat'];
	$alert="";
	$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
	$json_string = file_get_contents($reply);
	$parsed_json = json_decode($json_string);
	//var_dump($parsed_json);
	$comune="";
	$temp_c1 =$parsed_json->{'display_name'};

	if ($parsed_json->{'address'}->{'town'}) {
		$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'city'};

	}
		$comune .=$parsed_json->{'address'}->{'town'};
	//	if ($parsed_json->{'address'}->{'town'}) {

		echo "comune: ".$comune;

		$html = file_get_contents('http://151.12.58.144:8080/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&comune='.$comune);
		//echo $html;
		//$html = iconv('ASCII', 'UTF-8//IGNORE', $html);
		$html=utf8_decode($html);
	$html=str_replace("<![CDATA[","",$html);
	$html=str_replace("]]>","",$html);

		$doc = new DOMDocument;
		$doc->loadHTML($html);

		$xpa    = new DOMXPath($doc);
		//var_dump($doc);
		$divs0   = $xpa->query('//luogodellacultura');

		$divs   = $xpa->query('//luogodellacultura/proprieta');
		$divs1   = $xpa->query('//luogodellacultura/denominazione');
		$divs2   = $xpa->query('//luogodellacultura/descrizione');
		$divs3   = $xpa->query('//luogodellacultura/traduzione');
		$divs4   = $xpa->query('//luogodellacultura/orario/testostandard');
		$divs5   = $xpa->query('//info/sitoweb');
		$divs6   = $xpa->query('//info/email');
		$divs7   = $xpa->query('//info/telefono/testostandard');
		$divs8   = $xpa->query('//info/chiusuraSettimanale/testostandard');
		$divs9   = $xpa->query('//latitudine');
		$divs10   = $xpa->query('//longitudine');
	$diva=[];
	$diva1=[];
	$diva2=[];
	$diva3=[];
	$diva4=[];
	$diva5=[];
	$diva6=[];
	$diva7=[];
	$diva8=[];
	$diva9=[];
	$diva10=[];
	$count=0;
		foreach($divs as $div) {
				array_push($diva,$div->nodeValue);
	$count++;
		}

		foreach($divs1 as $div1) {

					array_push($diva1,$div1->nodeValue);
		}

		foreach($divs2 as $div2) {

					array_push($diva2,$div2->nodeValue);
		}
		foreach($divs3 as $div3) {
				$allerta3 .= "\n<br>".$div3->nodeValue;
					array_push($diva3,$div3->nodeValue);
		}
		foreach($divs4 as $div4) {

					array_push($diva4,$div4->nodeValue);
		}
		foreach($divs5 as $div5) {

					array_push($diva5,$div5->nodeValue);
		}
		foreach($divs6 as $div6) {

					array_push($diva6,$div6->nodeValue);
		}
		foreach($divs7 as $div7) {

					array_push($diva7,$div7->nodeValue);
		}
		foreach($divs8 as $div8) {

					array_push($diva8,$div8->nodeValue);
		}
		foreach($divs9 as $div9) {

					array_push($diva9,$div9->nodeValue);
		}
		foreach($divs10 as $div10) {

					array_push($diva10,$div10->nodeValue);
		}

	for ($i=0;$i<$count;$i++){

	$alert.= "Ente: ".$diva1[$i];
	$alert.= "\nProprietà: ".$diva[$i];
	$alert.= "\nDescrizione: ".$diva2[$i];
	if ($diva3[$i]!=NULL) $alert.= "\n ".$diva3[$i];
	if ($diva4[$i]!=NULL) $alert.= "\nApertura: ".$diva4[$i];
	if ($diva5[$i]!=NULL)$alert.= "\nSitoweb: ".$diva5[$i];
	if ($diva6[$i]!=NULL) $alert.= "\nEmail: ".$diva6[$i];
	if ($diva7[$i]!=NULL)$alert.= "\nTelefono: ".$diva7[$i];
	if ($diva8[$i]!=NULL)$alert.= "\nChiusura settimanale: ".$diva8[$i];
	//if ($diva9[$i]!=NULL)$alert.= "\nCoordinate: ".$diva9[$i].",".$diva10[$i];

  	if ($diva9[$i]!=NULL){
  	//  $alert.= "\nCoordinate: ".$diva9[$i].",".$diva10[$i];

  	  $longUrl = "http://www.openstreetmap.org/?mlat=".$diva9[$i]."&mlon=".$diva10[$i]."#map=19/".$diva9[$i]."/".$diva10[$i];

  	  $apiKey = API;

  	  $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
  	  $jsonData = json_encode($postData);

  	  $curlObj = curl_init();

  	  curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
  	  curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
  	  curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
  	  curl_setopt($curlObj, CURLOPT_HEADER, 0);
  	  curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
  	  curl_setopt($curlObj, CURLOPT_POST, 1);
  	  curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

  	  $response = curl_exec($curlObj);

  	  // Change the response json string to object
  	  $json = json_decode($response);

  	  curl_close($curlObj);
  	  //  $reply="Puoi visualizzarlo su :\n".$json->id;
  	  $shortLink = get_object_vars($json);
  	  //return $json->id;
  	  $alert .="Mappa:\n".$shortLink['id'];
  	  }
	$alert .= "\n\n";
  }

echo $alert;


?>
