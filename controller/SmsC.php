<?php

class SmsC {
    // Remplacer ces valeurs par celles de votre console Twilio
    private $sid = "AC0b7b850c6a3283e572e3bc257814c881"; 
    private $token = "800f10984dd133b79ec3418baefe9932";
    private $from = "+12525302651";

    public function sendSms($to, $message) {
        // Si les clés ne sont pas configurées, on simule l'envoi
        if ($this->sid === "VOTRE_ACCOUNT_SID") {
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
