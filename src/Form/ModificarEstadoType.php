<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ModificarEstadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estado', ChoiceType::class, [
                'choices' => [
                    'Pendiente' => 'Pendiente',
                    'Listo' => 'Listo',
                    'Finalizado' => 'Finalizado',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes seleccionar un estado para el producto',
                    ]),
                ],
                'empty_data' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
