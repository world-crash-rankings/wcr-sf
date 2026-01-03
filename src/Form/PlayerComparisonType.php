<?php

declare(strict_types=1);

namespace App\Form;

use App\Repository\PlayerRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, string>>
 */
class PlayerComparisonType extends AbstractType
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $players = $this->playerRepository->getPlayerChoices();

        $builder
            ->add('player1', ChoiceType::class, [
                'choices' => $players,
                'placeholder' => 'Select first player',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('player2', ChoiceType::class, [
                'choices' => $players,
                'placeholder' => 'Select second player',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Crash Clash!',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
