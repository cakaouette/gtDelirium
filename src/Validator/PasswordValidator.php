<?php

namespace App\Validator;

class PasswordValidator
{
    //TODO make something more standard
    public function validate($input): array {
        if (is_null($input)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe non vide");
        }
        if (!preg_match("~.{10,}~", $input)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe d'au moins 10 caractères");
        }
        if (!preg_match("~[a-z]~", $input)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins une lettre minuscule");
        }
        if (!preg_match("~[A-Z]~", $input)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins une lettre Majuscule");
        }
        if (!preg_match("~\\d+~", $input)) {
            return array("accept" => false, "msg" => "Chosisser un mot de passe avec au moins un chiffre");
        }
        return array("accept" => true, "msg" => "");
    }
}