<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<Country>
 */
class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('abr', TextType::class, [
                'label' => 'Olympics norm (3 letters)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 3, max: 3),
                ],
                'attr' => [
                    'maxlength' => 3,
                ],
            ])
            ->add('iso', TextType::class, [
                'label' => 'ISO norm (2 letters)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2, max: 2),
                ],
                'attr' => [
                    'maxlength' => 2,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
        ]);
    }
}
