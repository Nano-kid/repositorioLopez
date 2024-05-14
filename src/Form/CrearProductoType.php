<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class CrearProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes introducir una nombre para el producto',
                    ]),
                ]
            ])
            ->add('descripcion', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes introducir una descripcion para el producto',
                    ]),
                ]
            ])
            ->add('precio', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'step' => '0.05',
                ],
            ])
            ->add('descuento', PercentType::class, [
                'required' => false,
                'type' => 'integer', // Define el tipo de valor interno 
                'attr' => [
                    'min' => 0, // Valor mínimo permitido
                    'max' => 100, // Valor máximo permitido
                ],
            ])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
            ])
            ->add('foto', FileType::class, [
                'label' => 'Foto',
                'mapped' => false,
                //'required' => true,seria otra forma de indicar que es obligatorio 
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, seleccione una foto',
                    ]),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Por favor sube un archivo JPEG válido',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
