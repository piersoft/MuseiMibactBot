<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//$data=new getdata();
	// Instances the class

	/* If you need to manually take some parameters
	*  $result = $telegram->getData();
	*  $text = $result["message"] ["text"];
	*  $chat_id = $result["message"] ["chat"]["id"];
	*/


	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	if (strpos($text,'@MuseiMibactBot') !== false) $text=str_replace("@MuseiMibactBot ","",$text);

	if ($text == "/start" || $text == "Info") {
		$reply = "Benvenuto. Per ricercare un Museo, clicca sulla graffetta (📎) e poi 'posizione' oppure digita il nome del Comune. Verrà interrogato il DataBase Unico del Mibact utilizzabile con licenza CC-BY e verranno elencati fino a max 50 musei. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale, è stato realizzato da @piersoft e il codice sorgente per libero riuso si trova su https://github.com/piersoft/MuseiMibactBot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
$reply .="\nWelcome. To search for a Museum, click on the paper clip (📎) and then 'position' or type the name of the municipality. It will be questioned DataBase Unique Mibact used with the CC-BY license, and will be listed up to max 50 museums. At any time by writing / start you repeat this welcome message. This bot, unofficially, has been realized by @piersoft and the source code for free reuse is on https://github.com/piersoft/MuseiMibactBot. Its position is searched through the geocoder OpenStreetMap with Lic. ODbL.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
				$this->create_keyboard($telegram,$chat_id);
				exit;

		}

		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}
//elseif($text !=null)

		elseif(strpos($text,'🏛') === false){


			$location="Sto cercando i Musei del Comune di: / Searching for Town's Museums of: ".$text;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			sleep (1);
			$html = file_get_contents('http://dbunico20.beniculturali.it/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&quantita=50&comune='.$text);

		$html=str_replace("<![CDATA[","",$html);
		$html=str_replace("]]>","",$html);
		$html=str_replace("</br>","",$html);
		$html=str_replace("\n","",$html);
		$html=str_replace("&nbsp;","",$html);
		$html=str_replace(";"," ",$html);
		$html=str_replace(","," ",$html);
		if (strpos($html,'<mibac>') == false) {
			$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei censiti Mibact in questo luogo",'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
						$this->create_keyboard($telegram,$chat_id);
						exit;
		}

		$doc = new DOMDocument;
		$doc->loadHTML($html);

		$xpa    = new DOMXPath($doc);
			//var_dump($doc);
		$divsl   = $xpa->query('//codice[@sorgente="DBUnico 2.0"]');
		$divs0   = $xpa->query('//mibac');
		$divs   = $xpa->query('//mibac//luogodellacultura/proprieta');
		$divs1   = $xpa->query('//mibac//luogodellacultura/denominazione/nomestandard');
		$dival=[];
		$diva=[];
		$diva1=[];

		$count=0;
		foreach($divs0 as $div0) {
			$count++;
		}
		echo "Count: ".$count."\n";
		foreach($divsl as $divl) {

					array_push($dival,$divl->nodeValue);
		}

			foreach($divs as $div) {
					array_push($diva,$div->nodeValue);

			}

			foreach($divs1 as $div1) {

						array_push($diva1,$div1->nodeValue);
			}

		//$count=3;
if ($count > 50){
	$content = array('chat_id' => $chat_id, 'text' => "Troppe richieste, registringi la ricerca");
		$telegram->sendMessage($content);
		exit;
}
$option=[];
		for ($i=0;$i<$count;$i++){
		$alert.="\n\n";
		$alert.= $diva1[$i]."\n";
		$alert .="🏛 ".$dival[$i]."\n";
		$option[$i]=$dival[$i];
	//	$alert.= "Clicca per dettagli: /".$diva1[$i]."\n";
		$alert.="__________________";
	}

	//	echo $alert;

		$chunks = str_split($alert, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
//			$forcehide=$telegram->buildForceReply(true);
				//chiedo cosa sta accadendo nel luogo
	//		$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_markup' =>$forcehide,'disable_web_page_preview'=>true);
	$forcehide=$telegram->buildForceReply(true);
		//chiedo cosa sta accadendo nel luogo
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);

		}
		$optionf=array([]);
		for ($i=0;$i<$count;$i++){
			array_push($optionf,["🏛 ".$dival[$i]]);

		}
				$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su 🏛 per dettagli / Click on 🏛 for details]");
				$telegram->sendMessage($content);
			//	$this->create_keyboard($telegram,$chat_id);
			//	exit;

	//	$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎) / Send your position clicking 📎 or digit Town");
	//		$telegram->sendMessage($content);
/*

			 $reply = "Hai selezionato un comando non previsto. Ricordati che devi prima inviare la tua posizione cliccando sulla graffetta (📎) ";
			 $content = array('chat_id' => $chat_id, 'text' => $reply);
			 $telegram->sendMessage($content);

			 $log=$today. ";wrong command sent;" .$chat_id. "\n";
			 //$this->create_keyboard($telegram,$chat_id);

*/
	}elseif(strpos($text,'🏛') !== false){
		$text=str_replace("🏛 ","",$text);
		$text=str_replace("🏛","",$text);
	//	$text=str_replace("/","",$text);
	//	$text=str_replace("$$",")",$text);
	//	$text=str_replace("$","(",$text);
	//	$text=str_replace("___","-",$text);
	//	$text=str_replace("__","'",$text);
	//	$text=str_replace("_"," ",$text);
	//	$text=str_replace("22","\"",$text);
	//	$text=str_replace("E2809C","“",$text);

			$location="Sto cercando: / Searching for: ".$text;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			sleep (1);
	//		$text=urlencode($text);
			$text=str_replace("-","%2D",$text);
	//		$text=str_replace("'","%27",$text);
			$text=str_replace(" ","%20",$text);
	//		$text=str_replace("(","%28",$text);
	//		$text=str_replace(")","%29",$text);
	//		$text=str_replace("\"","%22",$text);
	//		$text=str_replace("“","%E2%80%9C",$text);



			$html = file_get_contents('http://dbunico20.beniculturali.it/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&quantita=50&id='.$text);

		$html=str_replace("<![CDATA[","",$html);
		$html=str_replace("]]>","",$html);
		$html=str_replace("</br>","",$html);
		$html=str_replace("\n","",$html);
		$html=str_replace("&nbsp;","",$html);
		$html=str_replace(";"," ",$html);
		$html=str_replace(","," ",$html);
		if (strpos($html,'<mibac>') == false) {
			$content = array('chat_id' => $chat_id, 'text' => "Nessun risultato / No result",'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
					$this->create_keyboard($telegram,$chat_id);
				exit;
		}

		$doc = new DOMDocument;
		$doc->loadHTML($html);

		$xpa    = new DOMXPath($doc);
			//var_dump($doc);
		$divsl   = $xpa->query('//codice[@sorgente="DBUnico 2.0"]');
		$divs0   = $xpa->query('//mibac');
		$divs   = $xpa->query('//mibac//luogodellacultura/proprieta');
		$divs1   = $xpa->query('//mibac//luogodellacultura/denominazione/nomestandard');
		$divs2   = $xpa->query('//mibac//luogodellacultura/descrizione/testostandard');
		$divs3   = $xpa->query('//mibac//luogodellacultura/descrizione/traduzioni');
		$divs4   = $xpa->query('//mibac//luogodellacultura/orario/testostandard');
		$divs5   = $xpa->query('//info/sitoweb');
		$divs6   = $xpa->query('//info/email');
		$divs7   = $xpa->query('//info/telefono/testostandard');
		$divs8   = $xpa->query('//chiusurasettimanale/testostandard');
		$divs9   = $xpa->query('//latitudine');
		$divs10   = $xpa->query('//longitudine');
		$divs11   = $xpa->query('//indirizzo/via-piazza');
		$divs12   = $xpa->query('//allegati/file/url');
		$divs13   = $xpa->query('//info/orario/testostandard');
		$dival=[];
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
		$diva11=[];
		$diva12=[];
			$diva13=[];
		$count=0;
		foreach($divs0 as $div0) {
			$count++;
		}
		echo "Count: ".$count."\n";
		foreach($divsl as $divl) {

					array_push($dival,$divl->nodeValue);
		}

			foreach($divs as $div) {
					array_push($diva,$div->nodeValue);

			}

			foreach($divs1 as $div1) {

						array_push($diva1,$div1->nodeValue);
			}

			foreach($divs2 as $div2) {

						array_push($diva2,$div2->nodeValue);
			}
			foreach($divs3 as $div3) {

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
			foreach($divs11 as $div11) {

						array_push($diva11,$div11->nodeValue);
			}
			foreach($divs12 as $div12) {

						array_push($diva12,$div12->nodeValue);
			}
			foreach($divs13 as $div13) {

						array_push($diva13,$div13->nodeValue);
			}

		//$count=3;
if ($count > 50){
	$content = array('chat_id' => $chat_id, 'text' => "Troppe richieste, registringi la ricerca");
		$telegram->sendMessage($content);
		exit;
}
		for ($i=0;$i<$count;$i++){
		$alert.="\n\n";
		$alert.= "Ente: ".$diva1[$i]."\n";
		$alert.= "Proprietà: ".$diva[$i]."\n";
		$alert.= "Descrizione: ".$diva2[$i]."\n";
		if ($diva3[$i]!=NULL) $alert.= "\n".$diva3[$i];
		if ($diva4[$i]!=NULL) $alert.= "\nApertura: ".$diva4[$i];
		if ($diva5[$i]!=NULL)$alert.= "\nSitoweb: ".$diva5[$i];
		if ($diva6[$i]!=NULL) $alert.= "\nEmail: ".$diva6[$i];
		if ($diva7[$i]!=NULL)$alert.= "\nTelefono: ".$diva7[$i];
		if ($diva11[$i]!=NULL)$alert.= "\nIndirizzo: ".$diva11[$i];
		if ($diva13[$i]!=NULL)$alert.= "\nApertura: ".$diva13[$i];
		if ($diva8[$i]!=NULL) $alert.= "\nChiusura settimanale: ".$diva8[$i];


		if ($dival[$i]!=NULL) {

			$longUrl = "http://www.beniculturali.it/mibac/opencms/MiBAC/sito-MiBAC/MenuPrincipale/LuoghiDellaCultura/Ricerca/index.html?action=show&idluogo=".$dival[$i];

			$apiKey = "AIzaSyABhW2DAsHzlYAyLrJxLLgCt0e6J735eYw";

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
			$shortLink = get_object_vars($json);
			$alert .="\nScheda completa: ".$shortLink['id'];
		//	$alert .="Foto: ".$diva12[$i]."\n\n";
	//		$content = array('chat_id' => $chat_id, 'text' => $diva12[$i]);
	//		$telegram->sendMessage($content);
		}

		if ($diva12[$i]!=NULL) {

			$longUrl = $diva12[$i];
			$apiKey = "AIzaSyABhW2DAsHzlYAyLrJxLLgCt0e6J735eYw";

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
			$shortLink = get_object_vars($json);
			$alert .="\nFoto/Video: ".$shortLink['id'];

		}
		if ($diva9[$i]!=NULL){
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
						$shortLinkMappa = get_object_vars($json);
						$alert .="\nMappa: ".$shortLinkMappa['id'];

					}

			$alert.="\n\n__________________";



	}

	//	echo $alert;

		$chunks = str_split($alert, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
//			$forcehide=$telegram->buildForceReply(true);
				//chiedo cosa sta accadendo nel luogo
	//		$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_markup' =>$forcehide,'disable_web_page_preview'=>true);
	$forcehide=$telegram->buildForceReply(true);
		//chiedo cosa sta accadendo nel luogo
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);

			$telegram->sendMessage($content);

		}
			if ($diva12[0]!=NULL) {
	//	$reply=$diva12[0];
		$reply=$shortLink['id'];
		$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
		}
	//		$telegram->buildKeyBoardHide(true);
			$this->create_keyboard($telegram,$chat_id);
			exit;
/*

			 $reply = "Hai selezionato un comando non previsto. Ricordati che devi prima inviare la tua posizione cliccando sulla graffetta (📎) ";
			 $content = array('chat_id' => $chat_id, 'text' => $reply);
			 $telegram->sendMessage($content);

			 $log=$today. ";wrong command sent;" .$chat_id. "\n";
			// $this->create_keyboard($telegram,$chat_id);

*/
	}





}


// Crea la tastiera
function create_keyboard($telegram, $chat_id)
 {
	 $forcehide=$telegram->buildKeyBoardHide(true);
	 $content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎) / Send your position clicking 📎 or digit Town", 'reply_markup' =>$forcehide);
	 $telegram->sendMessage($content);

 }




function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$response=$telegram->getData();
				$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
				$json_string = file_get_contents($reply);
				$parsed_json = json_decode($json_string);
				//var_dump($parsed_json);
				$comune="";
				$temp_c1 =$parsed_json->{'display_name'};

				if ($parsed_json->{'address'}->{'town'}) {
					$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
					$comune .=$parsed_json->{'address'}->{'town'};
				}else 	$comune .=$parsed_json->{'address'}->{'city'};

				if ($parsed_json->{'address'}->{'village'}) $comune .=$parsed_json->{'address'}->{'village'};
			//	$location="Comune di: ".$comune." tramite le coordinate che hai inviato: ".$lat.",".$lon;
				$location="Comune di / Town of: ".$comune;

				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";
				echo $comune;
					$html = file_get_contents('http://dbunico20.beniculturali.it/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&quantita=50&comune='.$comune);
					//echo $html;
					//$html = iconv('ASCII', 'UTF-8//IGNORE', $html);
			//		$html=utf8_decode($html);
		  	$html=str_replace("<![CDATA[","",$html);
		  	$html=str_replace("]]>","",$html);
  			$html=str_replace("</br>","",$html);
				$html=str_replace("\n","",$html);
				$html=str_replace("&nbsp;","",$html);
				$html=str_replace(";"," ",$html);
	 			$html=str_replace(","," ",$html);
				if (strpos($html,'<mibac>') == false) {
					$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei censiti Mibact in questo luogo / No Museum in this place",'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
								$this->create_keyboard($telegram,$chat_id);
								exit;
				}

				$doc = new DOMDocument;
				$doc->loadHTML($html);

				$xpa    = new DOMXPath($doc);
					//var_dump($doc);
				$divsl   = $xpa->query('//codice[@sorgente="DBUnico 2.0"]');
				$divs0   = $xpa->query('//mibac');
				$divs   = $xpa->query('//mibac//luogodellacultura/proprieta');
				$divs1   = $xpa->query('//mibac//luogodellacultura/denominazione/nomestandard');
				$divs9   = $xpa->query('//latitudine');
				$divs10   = $xpa->query('//longitudine');

				$dival=[];
				$diva=[];
				$diva1=[];
				$diva2=[];
				$diva9=[];
				$diva10=[];
				$count=0;
				foreach($divs0 as $div0) {
					$count++;
				}
				echo "Count: ".$count."\n";
				foreach($divsl as $divl) {

							array_push($dival,$divl->nodeValue);
				}

					foreach($divs as $div) {
							array_push($diva,$div->nodeValue);

					}

					foreach($divs1 as $div1) {

								array_push($diva1,$div1->nodeValue);
					}

					foreach($divs9 as $div9) {

								array_push($diva9,$div9->nodeValue);
					}
					foreach($divs10 as $div10) {

								array_push($diva10,$div10->nodeValue);
					}

				$option=[];
				for ($i=0;$i<$count;$i++){
				$alert.="\n\n";
				$alert.= $diva1[$i]."\n";
				$alert .="🏛 ".$dival[$i]."\n";
				$option[$i]=$dival[$i];
			//	$alert .="Clicca per dettagli: /".$diva1[$i]."\n";
				if ($diva9[$i]!=NULL){
					$lat10=floatval($diva9[$i]);
					$long10=floatval($diva10[$i]);
					$theta = floatval($lon)-floatval($long10);
					$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
					$dist = floatval(acos($dist));
					$dist = floatval(rad2deg($dist));
					$miles = floatval($dist * 60 * 1.1515 * 1.609344);


			//		$theta = $lon-$diva10[$i];
			//		$dist = sin(deg2rad($lat)) * sin(deg2rad($diva9[$i])) +  cos(deg2rad($lat)) * cos(deg2rad($diva9[$i])) * cos(deg2rad($theta));
			//		$dist = acos($dist);
			//		$dist = rad2deg($dist);
			//		$miles = $dist * 60 * 1.1515 * 1.609344;


					if ($miles >=1){
						$alert .="Distanza: ".number_format($miles, 2, '.', '')." Km\n";
					} else $alert .="Distanza: ".number_format(($miles*1000), 0, '.', '')." mt\n";


				}

					$alert.="__________________";



			}

			//	echo $alert;

				$chunks = str_split($alert, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
		    $forcehide=$telegram->buildForceReply(true);
		   	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
  			$telegram->sendMessage($content);

				}


		//		$content = array('chat_id' => $chat_id, 'text' => "Digita un Comune oppure invia la tua posizione tramite la graffetta (📎) / Send your position clicking 📎 or digit Town");
		//			$telegram->sendMessage($content);

					$optionf=array([]);
					for ($i=0;$i<$count;$i++){
						array_push($optionf,["🏛 ".$option[$i]]);

					}
							$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
							$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su 🏛 per dettagli / Click on 🏛 for details]");
							$telegram->sendMessage($content);
					//		$telegram->buildKeyBoardHide(true);
					exit;

					}


				}


?>
