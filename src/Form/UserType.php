<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3, max: 180),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $isEdit ? 'New Password (leave blank to keep current)' : 'Password',
                'required' => !$isEdit,
                'mapped' => false,
                'constraints' => $isEdit ? [] : [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 6),
                ],
            ])
            ->add('userRole', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                ],
                'expanded' => true,
                'required' => true,
                'mapped' => false,
            ])
        ;

        // Pre-populate role field when editing
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user instanceof User) {
                $roles = $user->getRoles();
                if (in_array('ROLE_SUPER_ADMIN', $roles, true)) {
                    $form->get('userRole')->setData('ROLE_SUPER_ADMIN');
                } elseif (in_array('ROLE_ADMIN', $roles, true)) {
                    $form->get('userRole')->setData('ROLE_ADMIN');
                } else {
                    $form->get('userRole')->setData('ROLE_USER');
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
