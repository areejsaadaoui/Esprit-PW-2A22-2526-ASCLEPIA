<?php
require_once __DIR__ . '/../config_api.php';

class SmsC {
    private $sid = TWILIO_SID; 
    private $token = TWILIO_TOKEN;
    private $from = TWILIO_FROM;

    public function sendSms($to, $message) {
        // Si les clés ne sont pas configurées, on simule l'envoi
        if ($this->sid === "VOTRE_ACCOUNT_SID" || empty($this->sid)) {
            error_log("SIMULATION SMS vers $to : $message");
            return true;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/" . $this->sid . "/Messages.json";
        
        $data = [
            'From' => $this->from,
            'To' => $to,
            'Body' => $message
        ];

        $post = http_build_query($data);
        $x = curl_init($url);
        
        curl_setopt($x, CURLOPT_POST, true);
        curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false); // Pour éviter les soucis de certificats en local
        curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($x, CURLOPT_USERPWD, "$this->sid:$this->token");
        curl_setopt($x, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($x);
        $err = curl_error($x);
        curl_close($x);

        if ($err) {
            error_log("Erreur SMS Twilio : " . $err);
            return false;
        } else {
            return true;
        }
    }
}
?>
