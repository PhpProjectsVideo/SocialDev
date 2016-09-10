<?php

namespace PhpProjects\SocialDev\UI\FormTypes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Choose a username for yourself',
            'required' => true,
            'attr' => [
                'placeholder' => 'Username',
            ],
        ]);
    }

}