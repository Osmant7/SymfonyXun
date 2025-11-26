<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;


class Mail
{
    public function send($to_email, $to_name, $subject, $template, $vars = null)
    {
        // récupération du template
        // dd(dirname(__DIR__).'/Mail/welcome.html');
        $content = file_get_contents(dirname(__DIR__).'/Mail/'.$template);
        
        // Récupère les variables facultatives
        if($vars) {
            foreach ($vars as $key => $var) {
                // dd($key);
                $content = str_replace('{'.$key.'}', $var, $content);
            }
        }

        // dd($content);
        

         $mj = new Client($_ENV['MJ_APIKEY_PUBLIC'], $_ENV['MJ_APIKEY_PRIVATE'], true, ['version' => 'v3.1']);

            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => "osmantoy035@gmail.com",
                            'Name' => "projetXun"
                        ],
                        'To' => [
                            [
                                'Email' => $to_email,
                                'Name' => $to_name
                            ]
                        ],
                        'TemplateID' => 7354201,
                        'TemplateLanguage' => true,
                        'Subject' => $subject,
                        'Variables' => [
                            'content' => $content
                        ]
                    ]
                ]
            ];

        $mj->post(Resources::$Email, ['body' => $body]);

    }
}


?>