<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Player;
use App\Entity\Zone;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Search form for filtering scores by player and/or zone
 *
 * @extends AbstractType<array{player: \App\Entity\Player|null, zone: \App\Entity\Zone|null}>
 */
class ScoreSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => 'name',
                'label' => 'Player',
                'required' => false,
                'placeholder' => 'All',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
            ])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => function (Zone $zone) {
                    return $zone->getId() . ' - ' . $zone->getName();
                },
                'label' => 'Zone',
                'required' => false,
                'placeholder' => 'All',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('z')
                        ->orderBy('z.id', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
        ]);
    }
}
