<?php

namespace App\Form;

use App\Entity\Categoria;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
            ->add('descripcion', TextareaType::class, [
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
            ->add('unidadVenta', ChoiceType::class, [
                'choices' => [
                    'Unidad' => 'unidad',
                    'Cantidad' => 'Cantidad',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes seleccionar la unidad de venta para el producto',
                    ]),
                ],
                'placeholder' => 'Selecciona una opción',
                'empty_data' => null,
            ])
            ->add('descuento', PercentType::class, [
                'required' => false,
                'symbol' => false,
                'type' => 'integer',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
            ]);

            if ($options['is_image_required']) {
                $builder->add('foto', FileType::class, [
                    'label' => 'Foto',
                    'mapped' => false,
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
                            'mimeTypesMessage' => 'Por favor sube un archivo JPEG/JPG/PNG/GIF válido',
                        ]),
                    ],
                ]);
            }
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
            'is_image_required' => false,
        ]);
    }
}

