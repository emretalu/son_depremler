<?php

error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set("Europe/Istanbul");
//ini_set('display_errors', '1');

$sunucu = "http://www.koeri.boun.edu.tr/scripts/lst9.asp";
$yesterday = date("Y-m-d", strtotime("yesterday"));
 
$sayfa       = file_get_contents($sunucu);
$baslangic   = stristr($sayfa,"--------------");
$bitis       = strpos($baslangic,"</pre>");
$depremler   = substr($baslangic,0,$bitis);
$depremler   = str_replace("--------------", "", $depremler);
file_put_contents("depremler/" . date("Y-m-d") . "-depremler.json", $depremler);
$data = file("depremler/" . date("Y-m-d") . "-depremler.json");

$filename = "depremler/" . $yesterday . "-depremler.txt";

$return = false;

foreach($data as $deprem) {	
	$deprem = preg_replace('!\s+!', ' ', $deprem);

	if($deprem != ""){
		$parcala = explode(" ", $deprem);
		if(count($parcala) == 12){
			if($parcala[0] == date("Y.m.d", strtotime("yesterday"))){				
				$icerik = "Tarih: " . $parcala[0] . " " . $parcala[1] . "\t Şiddet: " . $parcala[6] . "\t Şehir: " . str_replace(array("(", ")"),"",$parcala[9]) . " {" . $parcala[8] . "} \n";
				file_put_contents($filename, $icerik, FILE_APPEND);
				$return = true;
			}
		}
	}
}

if($return){
	require("PHPMailer/PHPMailerAutoload.php");

	$headers = "From: no-reply@yourhost.com \r\n Reply-To: no-reply@yourhost.com \r\n X-Mailer: PHP/" . phpversion();
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPDebug = 0;
	$mail->Debugoutput = 'html';
	$mail->Host = "mail.yourhost.com";
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = "no-reply@yourhost.com";
	$mail->Password = "yourpassword";
	$mail->SetFrom("noreply@gmail.com", "No-Reply");
	$mail->AddAddress('yourmail@gmail.com', 'Your Name');
	$mail->Subject = 'Latest Earthquakes';
	$mail->Body = "";
	$mail->AddAttachment($filename, $yesterday . "-depremler.txt");
	
	if(!$mail->Send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		echo "Send";
	}
}