<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserTest extends WebTestCase
{
    public function testSomething(): void
    {

        /*
            1. Créer un faux client (se comporte comme un navigateur) de pointer vers une URL
            2. Rempli les champs de mon formulaire d'inscription
            3. est-ce que tu peux regarder si  dans ma page j'ai le message d'alerte suivant : Votre compte est crée, veuillez vous connecter

        */

        // 1 .    
        $client = static::createClient();
        $client->request('GET', '/inscription');
        
        // 2. ( firstname, lastname, email, password, confirmation du password)
        $client->submitForm('Valider', [
            'register_user[email]' => 'julie@exemple.fr',
            'register_user[plainPassword][first]' => '123456',
            'register_user[plainPassword][second]' => '123456',
            'register_user[firstname]' => 'julie',
            'register_user[lastname]' => 'doe'
        ]);

        // Suivre les redirections ( car quand l'utilisateur s'inscrit il est redirigé sur la page de connexion et c'est sur cette page que s'affiche le message d'alerte donc on accompagne le faux utilisateur )
        // pour faire suivre le faux client, il faut également tester la redirection
        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();

        // 3.
        $this->assertSelectorExists('div:contains("Votre compte est crée, veuillez vous connecter")');

    }
}
