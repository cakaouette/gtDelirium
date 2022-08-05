<?php

namespace App\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//TODO use namespace and use instead of require once migration is over
//require_once('model/Manager/BossManager.php');
//use BossManager;
//require_once('model/Manager/AilmentManager.php');
//use AilmentManager;
//require_once('model/Manager/AilmentEnduranceManager.php');
//use AilmentEnduranceManager;
//require_once('model/Manager/WeaponManager.php');
//use WeaponManager;

final class DiscordController extends BaseController
{
    protected function __init() {
    }

    private function discordmsg($msg) {
      $ch = curl_init($_webHooks["Test"]);
      $msg = "payload_json=" . urlencode(json_encode($msg))."";

      if(isset($ch)) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
      }
    }
    
    public function api(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
      require("private/webHooks.php");
      $data = array("content" => "Your Content", "username" => "Webhooks");
      $curl = curl_init($_webHooks["Test"]);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));                                                           
      return $this->view->render($response, 'discord/alarm.twig',
              ['msg' => "allo",
               'hook' => $_webHooks["Test"],
               'result' => $result
              ]);
    }

}
