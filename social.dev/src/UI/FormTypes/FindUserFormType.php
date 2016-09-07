<?php

namespace PhpProjects\SocialDev\UI\FormTypes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for searching for a user
 */
class FindUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Search for user',
            'required' => true,
            'attr' => [
                'placeholder' => 'username',
            ],
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
    }


}