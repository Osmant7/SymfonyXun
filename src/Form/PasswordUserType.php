<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormError;

class PasswordUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actualPassword', PasswordType::class, [
                'label' => 'Votre mot de passe actuel',
                'attr' => [
                    'placeholder' => 'Indiquez votre mot de passe actuel'
                ],
                'mapped' => false
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new Length([
                        'min' => 4,
                        'max' => 30
                    ])
                ],
                'first_options'  => [
                    'label' => 'Votre nouveau mot de passe',
                    'attr' => [
                        'placeholder' => "Choisissez votre nouveau mot de passe"
                    ],
                    'hash_property_path' => 'password'
                    // permet de transiter le mot de passe saisis dans le formulaire jusqu'au controller de maniere encodé
                ],
                'second_options' => [
                    'label' => 'Confirmer votre nouveau mot de passe',
                    'attr' => [
                        'placeholder' => "Confirmer votre nouveau mot de passe"
                    ]
                ],
                'mapped' => false,
                // mapped => false permet de dire à symfony de pas chercher dans l'entité User un champ nommé plainPassword
            ])
            
            ->add('submit', SubmitType::class, [
                'label' => "Mettre à jour mon mot de passe",
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
           // ->addEventListener(// a quel moment je veux écouter ? // qu'est ce que je veux faire )
            ->addEventListener( FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $user = $form->getConfig()->getOptions()['data'];
                // dd($form->getConfig()->getOptions());
                $passwordHasher = $form->getConfig()->getOptions()['passwordHasher'];
                // 1. récuperer le mot de passe saisi par l'utilisateur et le comparer au mot de passe en BDD ( dans l'entité )
                $actualPwd = $form->get('actualPassword')->getData();
                $isValid = $passwordHasher->isPasswordValid(
                    $user,
                    $form->get('actualPassword')->getData()
                );
                dump($isValid);
                
                // 3. si c'est différent alors envoyé erreur
                if(!$isValid) {
                    $form->get('actualPassword')->addError(new FormError("Votre mot de passe actuel ne correspond pas à votre saisie !"));
                }
            }) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'passwordHasher' => null
        ]);
    }
}
