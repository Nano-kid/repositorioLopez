<?php

namespace App\Form;

use App\Entity\LineasVenta;
use App\Entity\Pedido;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AgregarLineaVentaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad', NumberType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingresa una cantidad',
                    'step' => 'any', // Permite tanto enteros como decimales
                ],
                'required' => true,
            ])
            /*->add('unidadVenta', HiddenType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debes introducir una unidad de venta',
                    ]),
                ]
            ])*/
            /*->add('descuento')
            ->add('total')
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
                'choice_label' => 'id',
            ])
            ->add('Pedido', EntityType::class, [
                'class' => Pedido::class,
                'choice_label' => 'id',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LineasVenta::class,
        ]);
    }
}
