<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Car;
use App\Entity\Strat;
use App\Entity\Zone;
use App\Form\Type\RichTextEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Strat>
 */
class StratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => function (Zone $zone) {
                    return $zone->getId() . ' - ' . $zone->getName();
                },
                'label' => 'Zone',
                'required' => true,
                'placeholder' => 'Select a zone',
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
            ])
            ->add('description', RichTextEditorType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('hz50', CheckboxType::class, [
                'label' => '50Hz',
                'required' => false,
            ])
            ->add('hz60', CheckboxType::class, [
                'label' => '60Hz',
                'required' => false,
            ])
            ->add('gc', CheckboxType::class, [
                'label' => 'GC',
                'required' => false,
            ])
            ->add('xbox', CheckboxType::class, [
                'label' => 'Xbox',
                'required' => false,
            ])
            ->add('ps2', CheckboxType::class, [
                'label' => 'PS2',
                'required' => false,
            ])
            ->add('bestDamage', IntegerType::class, [
                'label' => 'Best Damage',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('bestMulti', IntegerType::class, [
                'label' => 'Best Multi',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('bestTotal', IntegerType::class, [
                'label' => 'Best Total',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('cars', EntityType::class, [
                'class' => Car::class,
                'choice_label' => 'name',
                'label' => 'Cars',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Strat::class,
        ]);
    }
}
