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

	if ($text == "/start") {
		$reply = "Benvenuto. Per ricercare un museo, clicca sulla graffetta (📎) e poi 'posizione'. Verrà interrogato il DataBase Unico del Mibact utilizzabile con licenza CC-BY e verranno elencati fino a max 20 musei. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale, è stato realizzato da @piersoft e il codice sorgente per libero riuso si trova su https://github.com/piersoft/MuseiMibactBot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";

		}

		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}
//elseif($text !=null)

		else{

			 $reply = "Hai selezionato un comando non previsto. Ricordati che devi prima inviare la tua posizione";
			 $content = array('chat_id' => $chat_id, 'text' => $reply);
			 $telegram->sendMessage($content);

			 $log=$today. ";wrong command sent;" .$chat_id. "\n";
			 //$this->create_keyboard($telegram,$chat_id);
		 }


}


// Crea la tastiera
function create_keyboard($telegram, $chat_id)
 {
	 $forcehide=$telegram->buildKeyBoardHide(true);
	 $content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo specifico", 'reply_markup' =>$forcehide);
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
				$location="Comune di: ".$comune." tramite le tue coordinate: ".$lat.",".$lon;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";
				echo $comune;
					$html = file_get_contents('http://151.12.58.144:8080/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&quantita=20&comune='.$comune);
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
					$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei in questo luogo",'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
				}

				$doc = new DOMDocument;
				$doc->loadHTML($html);

				$xpa    = new DOMXPath($doc);
					//var_dump($doc);
				$divs0   = $xpa->query('//mibac');
				$divs   = $xpa->query('//mibac//luogodellacultura/proprieta');
				$divs1   = $xpa->query('//mibac//luogodellacultura/denominazione/nomestandard');
				$divs2   = $xpa->query('//mibac//luogodellacultura/descrizione/testostandard');
				$divs3   = $xpa->query('//mibac//luogodellacultura/descrizione/traduzioni');
				$divs4   = $xpa->query('//mibac//luogodellacultura/orario/testostandard');
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
				foreach($divs0 as $div0) {
					$count++;
				}
				echo "Count: ".$count."\n";

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

			  //$count=3;

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
				if ($diva8[$i]!=NULL)$alert.= "\nChiusura settimanale: ".$diva8[$i];
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
				$shortLink = get_object_vars($json);
				$alert .="\nMappa: ".$shortLink['id'];
				}

				$alert .= "\n";
			}
		//	$this->create_keyboard($telegram,$chat_id);
		echo $alert;
				$chunks = str_split($alert, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
					$forcehide=$telegram->buildForceReply(true);
						//chiedo cosa sta accadendo nel luogo
					$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_markup' =>$forcehide,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);

				}
			//	$this->create_keyboard($telegram,$chat_id);
	}


}

?>
