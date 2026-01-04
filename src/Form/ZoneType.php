<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Zone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Zone>
 */
class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('dmgWrKnown', CheckboxType::class, [
                'label' => 'WR damage known',
                'required' => false,
            ])
            ->add('glitch', ChoiceType::class, [
                'label' => 'Glitch Type',
                'choices' => [
                    'None' => null,
                    'Glitch' => 'glitch',
                    'Sink' => 'sink',
                ],
                'expanded' => true,
                'required' => false,
            ])
            ->add('top25Channel', UrlType::class, [
                'label' => 'WCR Top25 Channel',
                'required' => true,
            ])
            ->add('bestVidsChannel', TextType::class, [
                'label' => '10 Best Vids Channel',
                'required' => true,
            ])
            ->add('forum', UrlType::class, [
                'label' => 'Forum',
                'required' => true,
            ])
            ->add('starThresholds', CollectionType::class, [
                'entry_type' => StarType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}
