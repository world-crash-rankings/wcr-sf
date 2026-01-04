<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Car;
use App\Entity\Player;
use App\Entity\Score;
use App\Entity\Strat;
use App\Enum\Frequency;
use App\Enum\GlitchType;
use App\Enum\Platform;
use App\Enum\ProofType;
use App\Enum\Version;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for adding/editing scores
 *
 * @extends AbstractType<Score>
 */
class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $zoneId = $options['zone_id'];

        $builder
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => 'name',
                'label' => 'Player',
                'required' => true,
                'placeholder' => 'Select a player',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
            ])
            ->add('score', TextType::class, [
                'label' => 'Score',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex(
                        pattern: '/^\d+$/',
                        message: 'Score must be a positive integer'
                    ),
                ],
                'attr' => [
                    'min' => 0,
                    'type' => 'number',
                ],
            ])
            ->add('damage', TextType::class, [
                'label' => 'Damage',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        pattern: '/^\d*$/',
                        message: 'Damage must be a positive integer'
                    ),
                ],
                'attr' => [
                    'min' => 0,
                    'type' => 'number',
                ],
            ])
            ->add('multi', TextType::class, [
                'label' => 'Multi',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        pattern: '/^\d*$/',
                        message: 'Multi must be a positive integer'
                    ),
                ],
                'attr' => [
                    'min' => 0,
                    'type' => 'number',
                ],
            ])
            ->add('strat', EntityType::class, [
                'class' => Strat::class,
                'choice_label' => 'name',
                'label' => 'Strat',
                'required' => false,
                'placeholder' => 'Unknown',
                'query_builder' => function (EntityRepository $er) use ($zoneId) {
                    return $er->createQueryBuilder('s')
                        ->where('s.zone = :zoneId')
                        ->setParameter('zoneId', $zoneId)
                        ->orderBy('s.name', 'ASC');
                },
            ])
            ->add('car', EntityType::class, [
                'class' => Car::class,
                'choice_label' => 'name',
                'label' => 'Car',
                'required' => false,
                'placeholder' => 'Select a car',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('emulator', CheckboxType::class, [
                'label' => 'Emulator',
                'required' => false,
            ])
            ->add('formerWr', CheckboxType::class, [
                'label' => 'Former WR',
                'required' => false,
            ])
            ->add('proofType', EnumType::class, [
                'class' => ProofType::class,
                'label' => 'Proof Type',
                'required' => false,
                'placeholder' => 'None',
                'empty_data' => null,
            ])
            ->add('proofLink', UrlType::class, [
                'label' => 'Proof Link',
                'required' => false,
            ])
            ->add('platform', EnumType::class, [
                'class' => Platform::class,
                'label' => 'Platform',
                'required' => false,
                'placeholder' => 'Unknown',
                'empty_data' => null,
            ])
            ->add('version', EnumType::class, [
                'class' => Version::class,
                'label' => 'Version',
                'required' => false,
                'placeholder' => 'Unknown',
                'empty_data' => null,
            ])
            ->add('freq', EnumType::class, [
                'class' => Frequency::class,
                'label' => 'Frequency',
                'required' => false,
                'placeholder' => 'Unknown',
                'empty_data' => null,
            ])
            ->add('glitch', EnumType::class, [
                'class' => GlitchType::class,
                'label' => 'Glitch Type',
                'required' => false,
                'placeholder' => 'Unknown',
                'empty_data' => null,
            ])
            ->add('realisation', DateType::class, [
                'label' => 'Realisation Date (last day possible if inaccurate)',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'min' => '2002-01-01',
                ],
            ])
            ->add('inaccurate', TextType::class, [
                'label' => 'Inaccurate date (text to be displayed if date not accurate, let empty if irrelevant)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);

        $resolver->setRequired('zone_id');
        $resolver->setAllowedTypes('zone_id', 'int');
    }
}
